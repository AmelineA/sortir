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

class UpdateEventStateCommand extends Command
{
    protected static $defaultName = 'app:updateEventState';
    protected $em =null;

    public function __construct(EntityManagerInterface $em, ?string $name = null)
    {
        $this->em = $em;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Load dummy data in our database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventRepo = $this->em->getRepository(Event::class);
        $events = $eventRepo->updateState();
        if(!empty($events)){
            foreach ($events as $event){
                $event->setState('fermé');
                $this->em->persist($event);
            }
            $this->em->flush();
        }
    }


}