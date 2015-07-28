<?php
/*
 * (c) MASSIVE ART WebServices GmbH
 */

namespace Sulu\Bundle\ContactExtensionBundle\Import;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Sulu\Component\Content\Query\ContentQueryBuilderInterface;
use Sulu\Bundle\ContactBundle\Entity\AccountAddress;
use Sulu\Bundle\ContactBundle\Entity\AccountRepository;
use Sulu\Bundle\ContactExtensionBundle\Import\Exception\ImportException;

/**
 * Class for either completing missing data in database
 * or in a csv
 */
class DataCompleter
{
    /**
     * Constants
     */
    const DEBUG = true;
    const BATCH_SIZE = 20;
    const API_CALL_LIMIT_PER_SECOND = 9;
    const API_CALL_SLEEP_TIME = 2;

    /**
     * Geocode API
     *
     * @var string
     */
    private static $geocode_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=';

    /**
     * Options
     *
     * @var array
     */
    protected $options = array(
        'delimiter' => ';',
        'enclosure' => '"',
    );

    /**
     * Column mappings
     *
     * @var array
     */
    protected $columnMappings = array(
        'country' => 'LKZ',
        'street' => 'strasse',
        'zip' => 'PLZ',
        'state' => 'state',
        'city' => 'Ort',
    );

    /**
     * @var array
     */
    protected $headerData = array();

    /**
     * Language of the api results
     *
     * @var string
     */
    protected $locale;

    /**
     * Logfile
     *
     * @var array
     */
    protected $log;

    /**
     * Filepath of import file
     *
     * @var string
     */
    protected $file;

    /**
     * Limit execution
     *
     * @var int
     */
    protected $limit;

    /**
     * Currently processed row
     *
     * @var int
     */
    protected $currentRow;

    /**
     * Timestamp of the last api call
     *
     * @var int
     */
    protected $lastApiCallTime;

    /**
     * Number of api calls that are made in this sencond
     *
     * @var int
     */
    protected $lastApiCallCount;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    /**
     * @param EntityManagerInterface $em
     * @param AccountRepository $accountRepository
     * @param ContactRepository $contactRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        AccountRepository $accountRepository,
        ContactRepository $contactRepository
    ) {
        $this->log = array();

        $this->em = $em;
        $this->accountRepository = $accountRepository;
        $this->contactRepository = $contactRepository;
    }

    /**
     * Set limit of rows to process
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Set language of completion
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Set file to process
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Appends a string to a path + filename
     *
     * @param string $oldPath
     * @param string $postfix
     * @param bool $keepExtension
     *
     * @return string
     */
    protected function extendFileName($oldPath, $postfix, $keepExtension = true)
    {
        $parts = pathinfo($oldPath);
        $filename = $parts['dirname'] . '/' . $parts['filename'] . $postfix;
        if ($keepExtension) {
            $filename .= '.' . $parts['extension'];
        }

        return $filename;
    }

    /**
     * Process csv file
     */
    public function executeCsvCompletion()
    {
        $outputFileName = $this->extendFileName($this->file, '_processed');
        $output = fopen($outputFileName, "w");

        $this->processCsvLoop(
            $this->file,
            function ($data) use ($output) {

                $data = $this->completeAddress($data);

                fputcsv($output, $data, $this->options['delimiter'], $this->options['enclosure']);
            },
            function ($data) use ($output) {
                fputcsv($output, $data, $this->options['delimiter'], $this->options['enclosure']);
            }
        );
        fclose($output);

        $this->createLogFile();
    }

    /**
     * Gets ids of all records for the specific entity
     *
     * @param EntityRepository $entityRepository
     *
     * @return array
     */
    public function getIdsOfEntity($entityRepository)
    {
        /** @var ContentQueryBuilderInterface $qb */
        $qb = $entityRepository->createQueryBuilder('entity')
            ->select('entity.id');

        if ($this->limit) {
            $qb->setMaxResults($this->limit);
        }

        $ids = $qb->getQuery()->getScalarResult();

        return array_column($ids, 'id');
    }

    /**
     * Batch completes states of account or contacts
     *
     * @param EntityRepository $entityRepository
     * @param array $ids
     * @param callable $getAddressesName
     */
    public function batchCompleteStates($entityRepository, $ids, $getAddressesName)
    {
        $counter = 0;
        foreach ($ids as $id) {
            $counter++;
            $this->currentRow = $id;
            $entity = $entityRepository->find($id);
            $this->updateStateOfAddresses(call_user_func(array($entity, $getAddressesName)));

            // save
            if ($counter % self::BATCH_SIZE === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }
        $this->em->flush();
    }

    /**
     * Process csv file
     *
     * @param array $databaseOptions
     */
    public function executeDbCompletion($databaseOptions)
    {
        if (in_array('state', $databaseOptions)) {
            $this->debug("Completing states:\n");

            // complete account addresses
            $accountIds = $this->getIdsOfEntity($this->accountRepository);
            $this->debug(sprintf("\nFound %d accounts to complete addresses.\n", count($accountIds)));
            $this->batchCompleteStates($this->accountRepository, $accountIds, 'getAccountAddresses');

            // complete contact addresses
            $contactIds = $this->getIdsOfEntity($this->contactRepository);
            $this->debug(sprintf("\nFound %d contacts to complete addresses.\n", count($contactIds)));
            $this->batchCompleteStates($this->contactRepository, $contactIds, 'getContactAddresses');
        }
        $this->createLogFile();
    }

    /**
     * Updates state column in address entities by calling geolocation api of google
     *
     * @param array $addresses
     */
    protected function updateStateOfAddresses($addresses)
    {
        if (!$addresses || $addresses->isEmpty()) {
            return;
        }
        /** @var AccountAddress $accountAddress */
        foreach ($addresses as $accountAddress) {
            $this->debug('.', false);
            $address = $accountAddress->getAddress();

            $zip = $address->getZip();
            $country = $address->getCountry()->getName();

            // identify state by zip and country
            if ($zip && $country) {
                $state = $this->getStateByApiCall(array($zip, $country));
                if ($state) {
                    $address->setState($state);
                }
            }
        }
    }

    /**
     * Callback loop for processing a CSV file
     *
     * @param string $filename
     * @param callable $callback
     * @param callable $headerCallback
     */
    protected function processCsvLoop(
        $filename,
        callable $callback,
        callable $headerCallback
    ) {
        $row = 0;
        $this->currentRow = 0;
        $this->headerData = array();

        try {
            // load all Files
            $handle = fopen($filename, 'r');
        } catch (\Exception $e) {
            throw new NotFoundResourceException($filename);
        }

        while (($data = fgetcsv($handle, 0, $this->options['delimiter'], $this->options['enclosure'])) !== false) {
            try {
                // for first row, save headers
                if ($row === 0) {
                    $this->headerData = $data;
                    $this->headerCount = count($data);

                    $headerCallback($data);
                } else {

                    if ($this->headerCount !== count($data)) {
                        throw new ImportException('The number of fields does not match the number of header values');
                    }

                    $callback($data);
                }
            } catch (\Exception $e) {
                $this->debug(sprintf("ERROR while processing data row %d: %s \n", $row, $e->getMessage()));
            }

            // check limit and break loop if necessary
            $limit = $this->limit;
            if (!is_null($limit) && $row >= $limit) {
                break;
            }
            $row++;
            $this->currentRow = $row;

            if (self::DEBUG) {
                print(sprintf("%d ", $row));
            }
        }
        $this->debug("\n");
        fclose($handle);
    }

    /**
     * Function completes an address
     *
     * @param array $data
     *
     * @return array
     */
    protected function completeAddress($data)
    {
        $city = $this->getColumnValue('city', $data);
        $street = $this->getColumnValue('street', $data);
        $state = $this->getColumnValue('state', $data);
        $country = $this->getColumnValue('country', $data);
        $zip = $this->getColumnValue('zip', $data);
        if ($city || $street || $state || $country || $zip) {
            // address data is given
            // check if country is set
            if (!$country) {
                // we need to make an api call to fetch country
                $country = $this->getCountryByApiCall(array($street, $city, $zip, $state));

                $this->setColumnValue('country', $data, $country);
            }
        }

        return $data;
    }

    /**
     * Performs an api call and returns shorcode for a country
     *
     * @param array $data
     *
     * @return null|string
     */
    protected function getCountryByApiCall($data = array())
    {
        return $this->getDataByApiCall($data, array($this, 'getDataFromApiResultByKey'), array('country'));
    }

    /**
     * Performs an api call and returns shortcode for a country
     *
     * @param array $data
     *
     * @return null|string
     */
    protected function getStateByApiCall($data = array())
    {
        // FIXME: this is a workaround for a google geocode bug: austrian state names are only properly returned in
        //      short_name. for all other countries take long_name
        $resultKey = in_array('Austria', $data) ? 'short_name' : 'long_name';

        return $this->getDataByApiCall($data, array($this, 'getDataFromApiResultByKey'), array('administrative_area_level_1', $resultKey));
    }

    /**
     * Returns key from google geocode api result
     *
     * @param array $result
     * @param string $key
     * @param string $returnKey (either short_name or long_name)
     *
     * @return null|string (short_name)
     */
    protected function getDataFromApiResultByKey($result, $key, $returnKey = 'short_name')
    {
        if (property_exists($result, 'address_components')) {
            foreach ($result->address_components as $resultBlock) {
                if ($resultBlock->types[0] === $key) {
                    return $resultBlock->$returnKey;
                }
            }
        }

        return null;
    }

    /**
     * Performs an api call and returns shorcode for a country
     *
     * @param array $dataArray
     * @param callable $resultCallback will be called, passing the api-result object
     * @param array $callbackData Possibility to pass additional data to the callback
     *
     * @return string|null
     */
    protected function getDataByApiCall(
        $dataArray = array(),
        callable $resultCallback,
        $callbackData = array()
    ) {
        // limit api calls per second
        if ($this->lastApiCallTime == time()) {
            if ($this->lastApiCallCount >= static::API_CALL_LIMIT_PER_SECOND) {
                sleep(static::API_CALL_SLEEP_TIME);

                return $this->getDataByApiCall($dataArray, $resultCallback);
            }
            $this->lastApiCallCount++;
        } else {
            $this->lastApiCallCount = 1;
            $this->lastApiCallTime = time();
        }

        // remove null values
        $params = array_filter($dataArray);
        // create string
        $params = implode(',', $params);
        // avoid spaces
        $urlparams = urlencode($params);

        $apiResult = json_decode(file_get_contents(static::$geocode_url . $urlparams . '&language=' . $this->locale));

        $results = $apiResult->results;

        if (count($results) === 0) {
            $this->debug(sprintf("ERROR: No valid data found at data %d (by api)", $this->currentRow, $params));

            return null;
        }

        // take first result (if not unique)
        $result = $results[0];

        // get data by callback user function
        $callbackDataArray = array_merge(array($result), $callbackData);
        $data = call_user_func_array($resultCallback, $callbackDataArray);

        if (!$data) {
            $this->debug(sprintf("ERROR: No data found in result for data %d", $this->currentRow));

            return null;
        }

        if (count($results) > 1) {
            $this->debug(
                sprintf(
                    "Non unique result at row %d! chose data %s (params: %s)",
                    $this->currentRow,
                    $data,
                    $params
                )
            );
        }

        return $data;
    }

    /**
     * Prints messages if debug is set to true
     *
     * @param string $message
     * @param bool $addToLog
     */
    protected function debug($message, $addToLog = true)
    {
        if ($addToLog) {
            $this->log[] = $message;
        }
        if (self::DEBUG) {
            print($message);
        }
    }

    /**
     * Gets index of a column
     *
     * @param string $key
     *
     * @return int
     */
    protected function getColumnIndex($key)
    {
        // if in column mappings
        if (array_key_exists($key, $this->columnMappings)) {
            $key = $this->columnMappings[$key];
        }

        $index = array_search($key, $this->headerData);

        return $index;
    }

    /**
     * Returns value of a column
     *
     * @param string $key
     * @param array $data
     *
     * @return null|string
     */
    protected function getColumnValue($key, $data)
    {
        if (($index = $this->getColumnIndex($key)) !== false) {
            return $data[$index];
        }

        return null;
    }

    /**
     * Set value of a column
     *
     * @param string $key
     * @param array $data
     * @param mixed $value
     *
     * @throws \Exception
     */
    protected function setColumnValue($key, &$data, $value)
    {
        if (($index = $this->getColumnIndex($key)) !== false) {
            $data[$index] = $value;
        } else {
            throw new \Exception("column $key not set");
        }
    }

    /**
     * Creates a logfile in import-files folder
     */
    public function createLogFile()
    {
        if ($this->file) {
            $fileName = $this->extendFileName($this->file, '_log_' . time(), false);
        } else {
            $fileName = 'app/logs/datacompletion/' . time();
        }
        $file = fopen($fileName, 'w');
        fwrite($file, implode("\n", $this->log));
        fclose($file);
    }
}
