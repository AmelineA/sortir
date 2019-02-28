<?php
namespace App\Command;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class FixturesCommand extends Command
{
    protected static $defaultName = 'app:fixtures';
    protected $em =null;
    protected $encoder = null;

    public function __construct(UserPasswordEncoderInterface $encoder,EntityManagerInterface $em, ?string $name = null)
    {
        $this->encoder = $encoder;
        $this->em = $em;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Load dummy data in our database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       // parent::execute($input, $output);
       $io = new SymfonyStyle($input, $output);
       $io->text("Now loading fixtures ...");

        $faker = \Faker\Factory::create('fr_FR');

        $answer = $io->ask("Truncating all tables.... Sure? yes|no", 'no');
        if($answer !== "yes"){
            $io->text("Aborting");
            die();
        }

        $conn = $this->em->getConnection();
        //desactivate FK checks
        $conn->query('SET FOREIGN_KEY_CHECKS = 0');
        //truncate all tables
        $conn->query('TRUNCATE user');
        $conn->query('TRUNCATE site');
        $conn->query('TRUNCATE event');
        $conn->query('TRUNCATE event_user');
        $conn->query('TRUNCATE location');

        //reactivate FK checks
        $conn->query('SET FOREIGN_KEY_CHECKS = 1');

        //site (Nantes, Rennes, Niort)
        $sites = ['Nantes', 'Rennes', 'Niort'];
        $allSites = [];
        foreach($sites as $s){
            $site = new Site();
            $site->setName($s);
            $allSites[] = $site;
            $this->em->persist($site);
        }
        $this->em->flush();

        $allUser = [];
        //default user known from us
        $defaultUser = new User();
        $defaultUser->setUsername('FAG');
        $defaultUser->setName('FAG');
        $defaultUser->setFirstName('FAG');
        $defaultUser->setEmail('fag@email.fr');
        $defaultUser->setSite($faker->randomElement($allSites));
        $defaultUser->setTelephone('0101010101');
        $defaultUser->setPassword($this->encoder->encodePassword($defaultUser, $defaultUser->getUsername()));
        $defaultUser->setActivated(true);
        $defaultUser->setAddedOn(new \DateTime('now'));
        $allUser[] = $defaultUser;
        $this->em->persist($defaultUser);

        $adminUser = new User();
        $adminUser->setUsername('admin');
        $adminUser->setName('admin');
        $adminUser->setFirstName('admin');
        $adminUser->setEmail('admin@email.fr');
        $adminUser->setSite($faker->randomElement($allSites));
        $adminUser->setTelephone('0101010101');
        $adminUser->setPassword($this->encoder->encodePassword($adminUser, $adminUser->getUsername()));
        $adminUser->setActivated(true);
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setAddedOn(new \DateTime('now'));
        $allUser[] = $adminUser;
        $this->em->persist($adminUser);

        for ($i=0;$i<30; $i++){
            $user = new User();
            $user->setUsername($faker->unique()->userName);
            $user->setName($faker->name);
            $user->setFirstName($faker->firstName);
            $user->setEmail($faker->unique()->email);
            $user->setSite($faker->randomElement($allSites));
            $user->setTelephone($faker->randomNumber([10, true]));
            $user->setPassword($this->encoder->encodePassword($user, $user->getUsername()));
            $user->setActivated(true);
            $user->setAddedOn(new \DateTime('now'));
            $allUser[] = $user;
            $this->em->persist($user);
        }
        $this->em->flush();


        //création de vraies locations
        $barRennes=new Location();
        $barRennes->setName("WarpZone");
        $barRennes->setStreet("92 mail françois Mitterrand");
        $barRennes->setZipCode("35000");
        $barRennes->setCity("Rennes");
        $barRennes->setLatitude("48.108237");
        $barRennes->setLongitude("-1.6956463");

        $barNantes1=new Location();
        $barNantes1->setName("Altercafé");
        $barNantes1->setStreet("21 quai des Antilles");
        $barNantes1->setZipCode("44200");
        $barNantes1->setCity("Nantes");
        $barNantes1->setLatitude("47.2012309");
        $barNantes1->setLongitude("-1.5728972");

        $barNantes2=new Location();
        $barNantes2->setName("Zygobar");
        $barNantes2->setStreet("35 rue des olivettes");
        $barNantes2->setZipCode("44000");
        $barNantes2->setCity("Nantes");
        $barNantes2->setLatitude("47.2106138");
        $barNantes2->setLongitude("-1.5491828");

        $barNantes3=new Location();
        $barNantes3->setName("Baclerie");
        $barNantes3->setStreet("7 rue Baclerie");
        $barNantes3->setZipCode("44000");
        $barNantes3->setCity("Nantes");
        $barNantes3->setLatitude("47.2152648");
        $barNantes3->setLongitude("-1.5533777");

        $barNantes4=new Location();
        $barNantes4->setName("Delirium Café");
        $barNantes4->setStreet("19 allée Baco");
        $barNantes4->setZipCode("44000");
        $barNantes4->setCity("Nantes");
        $barNantes4->setLatitude("47.2133234");
        $barNantes4->setLongitude("-1.550475");

        $barNantes5=new Location();
        $barNantes5->setName("Bateau Lavoir");
        $barNantes5->setStreet("Canal St Felix");
        $barNantes5->setZipCode("44000");
        $barNantes5->setCity("Nantes");
        $barNantes4->setLatitude("47.2310484");
        $barNantes4->setLongitude("-1.5559399");

        $barNiort= new Location();
        $barNiort->setName('VLA');
        $barNiort->setStreet('30 rue Brisson');
        $barNiort->setZipCode('79000');
        $barNiort->setCity("Niort");
        $barNiort->setLatitude("46.3258318");
        $barNiort->setLongitude("-0.4640339");

        $locations = [$barNiort, $barNantes1, $barNantes2, $barNantes3, $barNantes4, $barNantes5, $barRennes];
        $allLocations = [];
        foreach ($locations as $l){
            $allLocations[] = $l;
            $this->em->persist($l);
        }
        $this->em->flush();


        //création d'events
//        $state = ['ouvert', 'fermé', 'en création', 'terminé', 'annulé'];
        $state = ['ouvert', 'fermé', 'terminé', 'annulé'];
        $allEvents = [];
        for($i=0; $i<150; $i++){
            $event = new Event();
            $event->setName($faker->unique()->name);
            $event->setOrganizer($faker->randomElement($allUser));
            $event->setState($faker->randomElement($state));
            $event->setSite($faker->randomElement($allSites));
            $event->setLocation($faker->randomElement($allLocations));
            $event->setDuration(120);
            $event->setMaxNumber(10);
            switch ($event->getState()){
                case 'ouvert':
                    $event->setRdvTime($faker->dateTimeBetween("+10days", "+30 days"));
                    $event->setSignOnDeadline($faker->dateTimeBetween("+2 days", "+5 days"));
                break;
                case 'fermé':
                    $event->setRdvTime($faker->dateTimeBetween("+10days", "+30 days"));
                    $event->setSignOnDeadline($faker->dateTimeBetween("-20 days", "-2 days"));
                break;
                case 'terminé':
                    $event->setRdvTime($faker->dateTimeBetween("-50days", "-2 days"));
                    $event->setSignOnDeadline($faker->dateTimeInInterval($event->getRdvTime(), "-5 days"));
                break;
                case 'annulé':
                    $event->setRdvTime($faker->dateTimeBetween("-15days", "+10 days"));
                    $event->setSignOnDeadline($faker->dateTimeInInterval($event->getRdvTime(), "-10 days"));
                break;
            }
//            $event->setRdvTime($faker->dateTimeBetween("-45 days", "+30 days"));
//            $event->setSignOnDeadline($faker->dateTimeBetween("-15 days", "+10 days"));
            $event->setDescription("Ca va être une super soirée. On va bien s'amuser. Ramenez vos écrans, ramenez vos souris, ramenez du chocolat, ça va être la guerre sur BattleField3. Ouais trop chouette. ");
            $allEvents[] = $event;
            $this->em->persist($event);
        }
        $this->em->flush();

        foreach ($allEvents as $e){
            for($j=0; $j<10; $j++){
                $e->addParticipant($faker->randomElement($allUser));
            }
            $this->em->persist($e);
        }
        $this->em->flush();

        $io->success('done !');
    }

}