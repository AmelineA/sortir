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

/**
 * is used for CRON task:
 * when signOnDeadline of an event is passed, the state of the event becomes "fermé".
 * when rdvtime of an event is passed, the state of the event becomes "passé"
 * Class UpdateEventStateCommand
 * @package App\Command
 */
class UpdateEventStateCommand extends Command
{
    protected static $defaultName = 'app:updateEventState';
    protected $em = null;

    /**
     * UpdateEventStateCommand constructor.
     * @param EntityManagerInterface $em
     * @param string|null $name
     */
    public function __construct(EntityManagerInterface $em, ?string $name = null)
    {
        $this->em = $em;
        parent::__construct($name);
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Load dummy data in our database');
    }


    /**
     * is used to execute the CRON task : change the state of the events according to the day's date
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventRepo = $this->em->getRepository(Event::class);
        //change the state of the events from "ouvert" to "fermé"
        $events1 = $eventRepo->updateStateToClosed();
        if (!empty($events1)) {
            foreach ($events1 as $event) {
                $event->setState('fermé');
                $this->em->persist($event);
            }
            $this->em->flush();
        }
        //change the state of the events from "fermé" to "terminé"
        $events2 = $eventRepo->updateStateToPassed();
        if (!empty($events2)) {
            foreach ($events2 as $event) {
                $event->setState('terminé');
                $this->em->persist($event);
            }
            $this->em->flush();
        }
    }


}