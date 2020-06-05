<?php

namespace App\Controller\Api\V1;

use App\Entity\Adress;
use App\Entity\User;
use App\Repository\AdressRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;

/**
 * @Route("/api/v1/user", name="api_v1_user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="userlist", methods={"GET"})
     */
    public function list(UserRepository $ur)
    {

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);

        $users = $ur->findAll();

        $data = $serializer->normalize($users, null, ['groups' => 'api_v1']);

        dump($users, $data);
        return $this->json($data);
    }

    /**
     * @Route("/add", name="userAdd", methods={"POST"})
     */
    public function add(Request $request)
    {
        $parsed_json = [];
        $content = $request->getContent();
        $parsed_json = json_decode($content, true);

        $firstname = $parsed_json['firstname'];
        $lastname = $parsed_json['lastname'];
        $birthday = $parsed_json['birthday'];
        $adresses = $parsed_json['adresses'];

        $em = $this->getDoctrine()->getManager();

        $user = new User;
        $user->setFirstname($firstname)->setLastname($lastname)->setBirthday(new DateTime());
        $em->persist($user);
        $em->flush();

        $adress = new Adress;
        $adress->setLine1($adresses[0]["line1"])->setLine2($adresses[0]["line2"]);
        $adress->setCity($adresses[0]["city"])->setCityCode($adresses[0]["cityCode"]);
        $adress->setUser($user);
        $em->persist($adress);
        $em->flush();

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);
        $data = $serializer->normalize($user, null, ['groups' => 'api_v1']);
        return $this->json($data);
    }

    /**
     * @Route("/{id}", name="userShow", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(int $id, UserRepository $ur)
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);

        $user = $ur->find($id);
        
        $data = $serializer->normalize($user, null, ['groups' => ['api_v1']]);
        dump($user, $data);
        return $this->json($data);
    }

    /**
     * @Route("/{id}/update", name="userUpdate", methods={"PUT"}, requirements={"id":"\d+"})
     */
    public function update(int $id,Request $request, UserRepository $ur)
    {
        $parsed_json = [];
        $content = $request->getContent();
        $parsed_json = json_decode($content, true);

        $firstname = $parsed_json['firstname'];
        $lastname = $parsed_json['lastname'];
        $birthday = $parsed_json['birthday'];

        $em = $this->getDoctrine()->getManager();

        $user = $ur->find($id);
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for'.$firstname.' '.$lastname
            );
        }

        $user->setFirstname($firstname)->setLastname($lastname)->setBirthday(new DateTime());
        $em->persist($user);
        $em->flush();


        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);
        $data = $serializer->normalize($user, null, ['groups' => ['api_v1']]);
        return $this->json($data);
    }

    /**
     * @Route("/{id}/delete", name="userDelete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete(int $id, UserRepository $ur)
    {
        $user = $ur->find($id);
        $em = $this->getDoctrine()->getManager();

        if (!$user) {
            throw $this->createNotFoundException('No user found for id '.$id);
        }
        
        $em->remove($user);
        $em->flush();

        return $this->json(true);
    }

    /**
     * @Route("/{id}/default", name="userDefaultAdress", methods={"PUT"}, requirements={"id":"\d+"})
     */
    public function userDefaultAdress(int $id, AdressRepository $ar, Request $request, UserRepository $ur)
    {

        $parsed_json = [];
        $content = $request->getContent();
        $parsed_json = json_decode($content, true);

        $user = $ur->find($id);
        $numberOfAdresses = count($user->getAdresses());

        for($i = 0; $i < $numberOfAdresses; $i++) {

            $adressId = $user->getAdresses()[$i]->getId();
            $defaut = $parsed_json['defaut'];
            $em = $this->getDoctrine()->getManager();
            $adress = $ar->find($adressId);
            $adress->setDefaut($defaut);
            $em->persist($adress);
            $em->flush();
        };
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);
        
        $data = $serializer->normalize($user, null, ['groups' => ['api_v1']]);
        return $this->json($data);
    }

}
