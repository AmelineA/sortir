<?php
namespace App\Command;

use App\Entity\Event;
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
        $defaultUser->setRoles(['ROLE_ADMIN']);
        $allUser[] = $defaultUser;
        $this->em->persist($defaultUser);

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
            $allUser[] = $user;
            $this->em->persist($user);
        }
        $this->em->flush();

        $state = ['ouvert', 'fermé', 'en création', 'terminé', 'annulé'];
        $allEvents = [];
        for($i=0; $i<150; $i++){
            $event = new Event();
            $event->setName($faker->unique()->name);
            $event->setOrganizer($faker->randomElement($allUser));
            $event->setState($faker->randomElement($state));
            $event->setSite($faker->randomElement($allSites));
            $event->setDuration(120);
            $event->setMaxNumber(10);
            $event->setRdvTime($faker->dateTimeBetween("-45 days", "+30 days"));
            $event->setSignOnDeadline($faker->dateTimeBetween("-15 days", "+10 days"));
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