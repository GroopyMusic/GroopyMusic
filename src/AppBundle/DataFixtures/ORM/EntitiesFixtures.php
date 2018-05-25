<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 18/05/2018
 * Time: 10:49
 */

namespace AppBundle\DataFixtures\ORM;


use AppBundle\Entity\Address;
use AppBundle\Entity\Artist;
use AppBundle\Entity\Artist_User;
use AppBundle\Entity\BaseContractArtist;
use AppBundle\Entity\Cart;
use AppBundle\Entity\ConcertPossibility;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\Genre;
use AppBundle\Entity\Hall;
use AppBundle\Entity\InvitationReward;
use AppBundle\Entity\Payment;
use AppBundle\Entity\Phase;
use AppBundle\Entity\Province;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\SponsorshipInvitation;
use AppBundle\Entity\Step;
use AppBundle\Entity\SuggestionTypeEnum;
use AppBundle\Entity\User;
use AppBundle\Entity\User_Reward;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class EntitiesFixtures extends Fixture implements ContainerAwareInterface
{
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        //PHASE_STEP
        // Phase 1
        $phase1 = new Phase();
        $phase1->setNum(1)->setName("Initiation");

        $step11 = new Step();
        $step11->setNum(1)->setDeadlineDuration(30)->setRequiredAmount(300)->setApproximateCapacity(100)->setMinTickets(100)->setMaxTickets(200)->setName("Concert 1")->setDescription("Blablabla");
        $step12 = new Step();
        $step12->setNum(2)->setDeadlineDuration(35)->setRequiredAmount(500)->setApproximateCapacity(100)->setMinTickets(100)->setMaxTickets(200)->setName("Concert 2")->setDescription("Blablabla");

        $phase1->addStep($step11)->addStep($step12);

        // Phase 2
        $phase2 = new Phase();
        $phase2->setNum(2)->setName("Expertise");

        $step21 = new Step();
        $step21->setNum(1)->setDeadlineDuration(40)->setRequiredAmount(800)->setApproximateCapacity(100)->setMinTickets(100)->setMaxTickets(200)->setName("Concert 3")->setDescription("Blablabla");
        $step22 = new Step();
        $step22->setDeadlineDuration(50)->setRequiredAmount(1000)->setNum(2)->setApproximateCapacity(100)->setMinTickets(100)->setMaxTickets(200)->setName("Concert 4")->setDescription("Blablabla");

        $phase2->addStep($step21)->addStep($step22);

        //persist && translation
        $manager->persist($phase1);
        $phase1->mergeNewTranslations();
        $manager->persist($phase2);
        $phase2->mergeNewTranslations();

        $step11->mergeNewTranslations();
        $step12->mergeNewTranslations();
        $step21->mergeNewTranslations();
        $step22->mergeNewTranslations();

        // Counterparts
        $cp11 = new CounterPart();
        $cp11->setPrice(10)->setMaximumAmount(50)->setDescription("Description")->setName("Place de concert normale");
        $step11->addCounterPart($cp11);
        $cp12 = new CounterPart();
        $cp12->setPrice(15)->setMaximumAmount(10)->setDescription("Description")->setName("Place de concert VIP");
        $step11->addCounterPart($cp12);
        $cp21 = new CounterPart();
        $cp21->setPrice(30)->setMaximumAmount(100)->setDescription("Description")->setName("Place de concert normale");
        $step12->addCounterPart($cp21);

        //persist
        $manager->persist($cp11);
        $cp11->mergeNewTranslations();
        $manager->persist($cp12);
        $cp12->mergeNewTranslations();
        $manager->persist($cp21);
        $cp21->mergeNewTranslations();


        //--USER--// //ARTIST//
        $userManager = $this->container->get('fos_user.user_manager');

        // Artist test account (credentials : artist@un-mute.be - test)
        $userA = new User();
        $userA->setUsername("artist")->setPlainPassword("test")->setEmail("artist@un-mute.be")->setFirstname("John")->setLastname("Doe")->setEnabled(true)->addRole('ROLE_SUPER_ADMIN')->setPreferredLocale('fr');
        $userA->setNewsletter(true);
        $userManager->updateUser($userA, true);

        $userB = new User();
        $userB->setUsername("artistB")->setPlainPassword("b")->setEmail("Alix.Lafond@un-mute.fr")->setFirstname("Alix")->setLastname("Lafond")->setEnabled(true)->addRole('ROLE_FAN')->setPreferredLocale('fr');
        $userB->setNewsletter(false);
        $userManager->updateUser($userB, true);

        $artistB = new Artist($phase1);
        $artistB->setArtistname('Odilon')->setVisible(true)->setShortDescription("short")->setBiography('long');

        $artist = new Artist($phase1);
        $artist->setArtistname('SeeUsoon')->setVisible(true)->setShortDescription("short")->setBiography('long');

        $artist_userA = new Artist_User();
        $artist_userA->setArtist($artist)->setUser($userA);

        $artist_userB = new Artist_User();
        $artist_userB->setArtist($artistB)->setUser($userB);

        $manager->persist($userA);
        $manager->persist($artist);
        $manager->persist($userB);
        $manager->persist($artistB);
        $artist->mergeNewTranslations();
        $artistB->mergeNewTranslations();
        $manager->persist($artist_userA);
        $manager->persist($artist_userB);

        // Test contracts
        $datetime = new \DateTime();
        $datetime->add(new \DateInterval('P40D'));

        $preferences = new ConcertPossibility();
        $c1 = new ContractArtist();
        $c1->setDate(new \DateTime())->setArtist($artist)->setDateEnd($datetime)->setRefunded(false)->setReality(null)->setFailed(false)->setMotivations("")->setStep($step11);
        $preferences->setDate($datetime);
        $c1->setPreferences($preferences);

        //contract artist sponsorship
        $preferences2 = new ConcertPossibility();
        $c2 = new ContractArtist();
        $c2->setDate(new \DateTime())->setArtist($artistB)->setDateEnd($datetime)->setMotivations("")->setStep($step11);
        $preferences2->setDate($datetime);
        $c2->setPreferences($preferences2);

        $manager->persist($c1);
        $manager->persist($c2);

        // Fan1 test account (credentials : fan@un-mute.be - test)
        $userF1 = new User();
        $userF1->setUsername("fan1")->setPlainPassword("test1")->setEmail("fan1@un-mute.be")->setFirstname("Elvis")->setLastname("Presley")->setEnabled(true)->setPreferredLocale('fr');
        $userManager->updateUser($userF1, true);
        $manager->persist($userF1); // persist

        //User for sponsorship
        $userF2 = new User();
        $userF2->setUsername("fan2")->setPlainPassword("test2")->setEmail("serge.Saada@un-mute.be")->setFirstname("Serge ")->setLastname("Saada")->setEnabled(true)->setPreferredLocale('fr');
        $userManager->updateUser($userF2, true);
        $manager->persist($userF2); // persist

        // Admin test account (credentials : admin@un-mute.be - test)
        $userAdmin = new User();
        $userAdmin->setUsername("admin")->setPlainPassword("test")->setEmail("admin@un-mute.be")->setFirstname("Kids")->setLastname("United")->setEnabled(true)->addRole('ROLE_SUPER_ADMIN')->setPreferredLocale('fr');
        $userManager->updateUser($userAdmin, true);
        $manager->persist($userAdmin); // persist


        //SUGGESTION_TYPE
        $type1 = new SuggestionTypeEnum();
        $type1->setLocale('fr')->setName('Demande de renseignement');
        $type1->mergeNewTranslations();

        $type2 = new SuggestionTypeEnum();
        $type2->setLocale('fr')->setName('Report de bug');
        $type2->mergeNewTranslations();

        $type3 = new SuggestionTypeEnum();
        $type3->setLocale('fr')->setName("Suggestion d'amélioration");
        $type3->mergeNewTranslations();

        $type4 = new SuggestionTypeEnum();
        $type4->setLocale('fr')->setName('Autre');
        $type4->mergeNewTranslations();

        $manager->persist($type1);
        $manager->persist($type2);
        $manager->persist($type3);
        $manager->persist($type4);

        //GENRES
        $genres_str = array("Rap", "Reggae", "Rock", "Pop", "Alternatif");

        $genres = array();

        foreach ($genres_str as $gs) {
            $genre = new Genre();
            $genre->setName($gs);
            $genres[] = $genre;
            $manager->persist($genre);
            $genre->mergeNewTranslations();
        }

        //PROVINCES
        $prov_str = array("Bruxelles", "Brabant Wallon", 'Hainaut', 'Namur', 'Liège', 'Luxembourg');

        $provinces = array();

        foreach ($prov_str as $ps) {
            $province = new Province();
            $province->setName($ps);
            $provinces[] = $province;
            $manager->persist($province);
            $province->mergeNewTranslations();
        }

        // Halls
        $address = new Address();
        $address->setCity('Brussels')->setCountry('Belgium')->setNumber(20)->setStreet('Rue de la Fontaine Dieu')->setZipcode('5310');

        $hall1 = new Hall();
        $hall1->setName('Salle2')->setAddress($address)->setCapacity(100)->setDelay(60)->setPrice(300)->setProvince($provinces[1]);
        $step11->addHall($hall1);

        $manager->persist($hall1);

        //Sponsorship//
        $sponsorshipInvitation1 = new SponsorshipInvitation(new \DateTime(),
            'acace.cochet@email.com', 'lol', $userF2, $c2,
            'aZlb1S6vxD9hJglmU9ECYBBct639hT9Q');
        $manager->persist($sponsorshipInvitation1);

        //userfan2 pay for seeUSoon concert//
        $cart1 = new Cart();
        $cf1 = new ContractFan($c1);
        $payment1 = new Payment();
        $purchase1 = new Purchase();

        $userF2->addCart($cart1);
        $cart1->addContract($cf1);
        $payment1->setContractArtist($c1)->setContractFan($cf1)->setDate(new \DateTime())->setChargeId('chargeID')->setRefunded(false)->setAmount(12)->setUser($userF2);
        $c1->addPayment($payment1)->addContractsFan($cf1);
        $purchase1->setCounterpart($cp11);
        $cf1->addPurchase($purchase1)->setPayment($payment1);

        $manager->persist($cart1);
        $manager->persist($cf1);
        $manager->persist($payment1);
        $manager->persist($purchase1);

        //REWARD
        $invitationReward1 = new InvitationReward();
        $invitationReward1->setValidityPeriod(50)->setMaxUse(5);
        $invitationReward1->setEndDate(new \DateTime())->setStartDate(new \DateTime());

        //USER_REWARD
        $user_reward1 = new User_Reward($invitationReward1, $userF2);
        $user_reward1->setActive(true);

        $manager->persist($invitationReward1);
        $manager->persist($user_reward1);

        //flush
        $manager->flush();

    }
}