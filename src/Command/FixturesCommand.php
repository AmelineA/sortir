<?php
namespace App\Command;

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

        //reactivate FK checks
        $conn->query('SET FOREIGN_KEY_CHECKS = 1');

        //$allUser = [];

        //site (Nantes, Rennes,, Niort)
            $site = new Site();
            $site->setName('Nantes');
            $this->em->persist($site);
            $this->em->flush();

        for ($i=0;$i<30; $i++){
            $user = new User();
            $user->setUsername($faker->unique()->userName);
            $user->setName($faker->name);
            $user->setFirstName($faker->firstName);
            $user->setEmail($faker->unique()->email);
            $user->setSite($site);
            $user->setTelephone($faker->randomNumber([10, true]));
            $user->setPassword($this->encoder->encodePassword($user, $user->getUsername()));
            $user->setActivated(true);
           // $allUser[] = $user;
            $this->em->persist($user);
        }

        $this->em->flush();

        $io->success('done !');

    }


}