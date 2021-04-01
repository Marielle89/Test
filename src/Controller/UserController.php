<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Country;
use App\Entity\Operator;
use App\Entity\User;
use App\Entity\Phone;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class UserController extends AbstractController
{
    /**
     * @Route("api/users/{id}", name="show_user", methods={"GET"})
     * @param User $user
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function show(User $user, SerializerInterface $serializer): JsonResponse
    {
        $response = new JsonResponse();
        $normalizedUser = $serializer->normalize($user, null, $this->getDefaultContext());
        $response->setData($normalizedUser);

        return  $response;
    }

    /**
     * @Route("api/users", name="create_user", methods={"POST"}, defaults={"_format": "json"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $entityManager
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $newUser = $serializer->deserialize($request->getContent(), User::class, 'json');

        /** @var ConstraintViolationList $errors */
        $errors = $validator->validate($newUser);
        if ($errors->count()) {
            throw new BadRequestHttpException($errors->get(0)->getPropertyPath() . $errors->get(0)->getMessage());
        }

        $entityManager->persist($newUser);
        $entityManager->flush();

        $entityAsArray = $serializer->normalize($newUser, null, $this->getDefaultContext());

        return  new JsonResponse($entityAsArray);

    }

    /**
     * @Route("api/users/{id}/add-phone/{number<380\d{9}>}", name="add_phone", methods={"POST"})
     * @param User $user
     * @param string $number
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function addPhone(User $user, string $number, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $matches = PhoneController::parsePhoneNumber($number);
        list(, $countryCode, $operatorCode, $phoneNumber) = $matches;
        $country = $entityManager->getRepository(Country::class)->findOneBy(['code' => $countryCode]);
        if (!$country instanceof Country) {
            throw $this->createNotFoundException('The phone does not exist');
        }

        $operator = $entityManager->getRepository(Operator::class)->findOneBy(['code' => $operatorCode]);
        if (!$operator instanceof Operator) {
            throw $this->createNotFoundException('The phone does not exist');
        }

        $existingPhone = $entityManager->getRepository(Phone::class)->findOneBy(['operator' => $operator, 'number' => $phoneNumber]);

        if ($existingPhone instanceof Phone) {
            throw new LogicException('The phone exist');
        }

        $phone = new Phone();
        $phone->setCountry($country)
            ->setOperator($operator)
            ->setNumber($phoneNumber)
            ->createDate();
        $user->addPhone($phone);

        $entityManager->flush();

        return (new JsonResponse($serializer->normalize($user, null, $this->getDefaultContext())));
    }

    /**
     * @Route("api/users/{id}", name="delete_user", methods={"DELETE"})
     * @param User $user
     * @param EntityManagerInterface $entityManager
     *
     * @return JsonResponse
     */
    public function delete(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return (new JsonResponse())->setStatusCode(204);
    }

    private function getDefaultContext(): array
    {
        return [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractNormalizer::ATTRIBUTES => ['name', 'birthday', 'phoneNumbers'],
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getName();
            },
            DateTimeNormalizer::FORMAT_KEY => 'd-m-Y'
        ];
    }
}
