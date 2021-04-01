<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\Country;
use App\Entity\Operator;
use App\Entity\Phone;
use App\Entity\User;
use Datetime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Exception;

class FillDB extends Command
{
    protected static $defaultName = 'app:fill-db';

    private EntityManagerInterface $entityManager;
    private array $phones = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Add data to DB');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting add data to DB...');

        $operatorRepository = $this->entityManager->getRepository(Operator::class);
        $countryRepository = $this->entityManager->getRepository(Country::class);

        /** @var Country $country */
        $country = $countryRepository->findOneBy(['id'=>'1']);
        $operators = [
            $operatorRepository->findOneBy(['id'=>'1']),
            $operatorRepository->findOneBy(['id'=>'2']),
            $operatorRepository->findOneBy(['id'=>'3']),
            $operatorRepository->findOneBy(['id'=>'4'])
        ];

        $io->progressStart(2000);

        for ($i = 0; $i < 2000; $i++) {

            $user = new User();
            $this->entityManager->persist($user);
            $user->createDate();
            $user->updateDate();
            $user->setName('user '. $i);

            $start = strtotime('1 January 1950');
            $end = strtotime("31 March 2010");

            $timestamp = mt_rand($start, $end);
            $birthday = (new Datetime())->setTimestamp($timestamp);
            $user->setBirthday($birthday);

            $phonesCount = mt_rand(1, 3);
            for ($j = 0; $j < $phonesCount; $j++) {
                $phone = new Phone();
                $phone->setCountry($country);
                $operatorId = mt_rand(0, 3);
                /** @var Operator $operator */
                $operator = $operators[$operatorId];
                $phone->setOperator($operators[$operatorId]);

                $number = (string)mt_rand(1000000, 9999999);

                while(in_array($operator->getCode() . $number, $this->phones)) {
                    $number = (string)mt_rand(1000000, 9999999);
                }
                $this->phones[] = $operator->getCode() . $number;
                $phone->setNumber($number);
                $phone->setBalance(mt_rand(-50, 150));
                $phone->createDate();
                $phone->updateDate();
                $user->addPhone($phone);
            }

            $this->entityManager->flush();

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();
        $io->success('Command finish!');

        return 0;
    }
}
