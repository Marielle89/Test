<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Operator;
use App\Entity\Phone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use LogicException;

class PhoneController extends AbstractController
{
    /**
     * @Route("api/phones/{number<380\d{9}>}/topup", name="topup_phone", methods={"POST"})
     * @param string $number
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function topup(string $number, Request $request, EntityManagerInterface $entityManager): Response
    {
        $matches = self::parsePhoneNumber($number);
        list(, , $operatorCode, $phoneNumber) = $matches;

        $amount = (float)$request->get('amount', 0);
        if (($amount <= 0) && ($amount > 100)) {
            throw new LogicException('Amount must be between 0 and 100');
        }

        $operator = $entityManager->getRepository(Operator::class)->findOneBy(['code' => $operatorCode]);
        if (!$operator instanceof Operator) {
            throw $this->createNotFoundException('The phone does not exist');
        }

        $phone = $entityManager->getRepository(Phone::class)->findOneBy(['operator' => $operator, 'number' => $phoneNumber]);

        if (!$phone instanceof Phone) {
            throw $this->createNotFoundException('The phone does not exist');
        }

        $phone->setBalance($phone->getBalance() + $amount);

        $entityManager->flush();

        return (new Response())->setStatusCode(204);
    }

    public static function parsePhoneNumber(string $number): array
    {
        preg_match('/^(\d{3})(\d{2})(\d{7})$/', $number, $matches);

        return $matches;
    }
}
