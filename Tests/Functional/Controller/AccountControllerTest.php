<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\ContactBundle\Tests\Functional\Controller;

use DateTime;
use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ContactBundle\Entity\AccountAddress;
use Sulu\Bundle\ContactBundle\Entity\AccountContact;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\Country;
use Sulu\Bundle\ContactBundle\Entity\Email;
use Sulu\Bundle\ContactBundle\Entity\EmailType;
use Sulu\Bundle\ContactBundle\Entity\Note;
use Sulu\Bundle\ContactBundle\Entity\Phone;
use Sulu\Bundle\ContactBundle\Entity\PhoneType;
use Sulu\Bundle\ContactBundle\Entity\Fax;
use Sulu\Bundle\ContactBundle\Entity\FaxType;
use Sulu\Bundle\ContactBundle\Entity\Url;
use Sulu\Bundle\ContactBundle\Entity\UrlType;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class AccountControllerTest extends SuluTestCase
{
    /**
     * @var Account
     */
    private $account;

    /**
     * @var Account
     */
    private $childAccount;

    /**
     * @var Account
     */
    private $parentAccount;


    public function setUp()
    {
        $this->purgeDatabase();
        $this->em = $this->db('ORM')->getOm();
        $this->initOrm();
    }

    private function initOrm()
    {
        $account = new Account();
        $account->setName('Company');
        $account->setType(Account::TYPE_BASIC);
        $account->setDisabled(0);
        $account->setPlaceOfJurisdiction('Feldkirch');

        $parentAccount = new Account();
        $parentAccount->setName('Parent');
        $parentAccount->setType(Account::TYPE_BASIC);
        $parentAccount->setDisabled(0);
        $parentAccount->setPlaceOfJurisdiction('Feldkirch');

        $childAccount = new Account();
        $childAccount->setName('Child');
        $childAccount->setType(Account::TYPE_BASIC);
        $childAccount->setDisabled(0);
        $childAccount->setPlaceOfJurisdiction('Feldkirch');
        $childAccount->setParent($parentAccount);

        $this->account = $account;
        $this->childAccount = $childAccount;
        $this->parentAccount = $parentAccount;

        $urlType = new UrlType();
        $urlType->setName('Private');

        $this->urlType = $urlType;

        $url = new Url();
        $url->setUrl('http://www.company.example');

        $this->url = $url;
        $url->setUrlType($urlType);
        $account->addUrl($url);

        $this->emailType = new EmailType();
        $this->emailType->setName('Private');

        $this->email = new Email();
        $this->email->setEmail('office@company.example');
        $this->email->setEmailType($this->emailType);
        $account->addEmail($this->email);

        $phoneType = new PhoneType();
        $phoneType->setName('Private');

        $this->phoneType = $phoneType;

        $phone = new Phone();
        $phone->setPhone('123456789');
        $phone->setPhoneType($phoneType);
        $account->addPhone($phone);

        $faxType = new FaxType();
        $faxType->setName('Private');

        $this->faxType = $faxType;

        $fax = new Fax();
        $fax->setFax('123654789');
        $fax->setFaxType($faxType);
        $account->addFax($fax);

        $country = new Country();
        $country->setName('Musterland');
        $country->setCode('ML');

        $this->country = $country;

        $addressType = new AddressType();
        $addressType->setName('Private');

        $this->addressType = $addressType;

        $address = new Address();
        $address->setStreet('Musterstraße');
        $address->setNumber('1');
        $address->setZip('0000');
        $address->setCity('Musterstadt');
        $address->setState('Musterland');
        $address->setCountry($country);
        $address->setAddition('');
        $address->setAddressType($addressType);
        $address->setBillingAddress(true);
        $address->setPrimaryAddress(true);
        $address->setDeliveryAddress(false);
        $address->setPostboxCity("Dornbirn");
        $address->setPostboxPostcode("6850");
        $address->setPostboxNumber("4711");
        $address->setNote("note");

        $this->address = $address;

        $accountAddress = new AccountAddress();
        $accountAddress->setAddress($address);
        $accountAddress->setAccount($account);
        $accountAddress->setMain(true);
        $account->addAccountAddresse($accountAddress);
        $address->addAccountAddresse($accountAddress);

        $contact = new Contact();
        $contact->setFirstName("Vorname");
        $contact->setLastName("Nachname");
        $contact->setMiddleName("Mittelname");
        $contact->setDisabled(0);
        $contact->setFormOfAddress(0);

        $accountContact = new AccountContact();
        $accountContact->setContact($contact);
        $accountContact->setAccount($account);
        $accountContact->setMain(true);
        $account->addAccountContact($accountContact);

        $note = new Note();
        $note->setValue('Note');
        $account->addNote($note);

        $this->em->persist($account);
        $this->em->persist($childAccount);
        $this->em->persist($parentAccount);
        $this->em->persist($urlType);
        $this->em->persist($url);
        $this->em->persist($this->emailType);
        $this->em->persist($accountContact);
        $this->em->persist($this->email);
        $this->em->persist($phoneType);
        $this->em->persist($phone);
        $this->em->persist($country);
        $this->em->persist($addressType);
        $this->em->persist($address);
        $this->em->persist($accountAddress);
        $this->em->persist($note);
        $this->em->persist($faxType);
        $this->em->persist($fax);
        $this->em->persist($contact);

        $this->em->flush();
    }

    public function testTriggerAction()
    {

        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/accounts/' . $this->account->getId() .'?action=convertAccountType&type=lead'
        );

        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertEquals($this->account->getId(), $response->id);
        $this->assertEquals(1, $response->type);

    }

    public function testTriggerActionUnknownTrigger()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/accounts/' . $this->account->getId() .'?action=xyz&type=lead'
        );

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testTriggerActionUnknownEntity()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/accounts/999?action=convertAccountType&type=lead'
        );

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
