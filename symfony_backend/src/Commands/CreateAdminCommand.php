<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App\Commands;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\RolesManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use function Symfony\Component\String\u;

class CreateAdminCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'app:create-admin';

    /** @var SymfonyStyle */
    private SymfonyStyle $io;

    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /** @var UserPasswordEncoderInterface */
    private UserPasswordEncoderInterface $passwordEncoder;

    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var RolesManager */
    private RolesManager $rolesManager;

    public function __construct
    (
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $encoder,
        UserRepository $userRepository,
        RolesManager $rolesManager

    )
    {
        parent::__construct();
        $this->entityManager = $manager;
        $this->passwordEncoder = $encoder;
        $this->rolesManager = $rolesManager;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Creates admin  and store in database')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the new admin')
            ->addArgument('password', InputArgument::REQUIRED, 'The plain password of the new admin')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the new admin')
            ->addOption('admin', true, InputOption::VALUE_OPTIONAL, 'If set, the user is created as an administrator');
    }

    /**
     * This optional method is the first one executed for a command after configure()
     * and is useful to initialize properties based on the input arguments and options.
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * This method is executed after initialize() and before execute(). Its purpose
     * is to check if some of the options/arguments are missing and interactively
     * ask the user for those values.
     *
     * This method is completely optional. If you are developing an internal console
     * command, you probably should not implement this method because it requires
     * quite a lot of work. However, if the command is meant to be used by external
     * users, this method is a nice way to fall back and prevent errors.
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (
            null !== $input->getArgument('username')
            && null !== $input->getArgument('password')
            && null !== $input->getArgument('email')
        ) {
            return;
        }

        $this->io->title('Add Admin Command Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:add-admin username password email@example.com',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        // Ask for the username if it's not defined
        $username = $input->getArgument('username');
        if (null !== $username) {
            $this->io->text(' > <info>Username</info>: ' . $username);
        } else {
            $username = $this->io->ask('Username', null);
            $input->setArgument('username', $username);
        }

        // Ask for the password if it's not defined
        $password = $input->getArgument('password');
        if (null !== $password) {
            $this->io->text(' > <info>Password</info>: ' . u('*')->repeat(u($password)->length()));
        } else {
            $password = $this->io->askHidden('Password (your type will be hidden)');
            $input->setArgument('password', $password);
        }

        // Ask for the email if it's not defined
        $email = $input->getArgument('email');
        if (null !== $email) {
            $this->io->text(' > <info>Email</info>: ' . $email);
        } else {
            $email = $this->io->ask('Email', null);
            $input->setArgument('email', $email);
        }
    }

    /**
     * This method is executed after interact() and initialize(). It usually
     * contains the logic to execute to complete this command task.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('add-admin-command');

        $username = $input->getArgument('username');
        $plainPassword = $input->getArgument('password');
        $email = $input->getArgument('email');
        $isAdmin = $input->getOption('admin');

        // create the user and encode its password
        $user = new User();
        $user->setName($username);
        $user->setEmail($email);
        $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($encodedPassword);
        $user->setRole($this->rolesManager->findOrDefault(1));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success(sprintf('%s was successfully created: %s (%s)',
            $isAdmin ? 'Administrator user' : 'User', $user->getUsername(), $user->getEmail()));

        $event = $stopwatch->stop('add-admin-command');

        if ($output->isVerbose()) {
            $this->io->comment(sprintf('New admin database id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB',
                $user->getId(), $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return Command::SUCCESS;
    }
}