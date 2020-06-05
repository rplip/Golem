<?php

namespace App\Controller\Api\V1;

use App\Entity\Adress;
use App\Repository\UserRepository;
use App\Repository\AdressRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;

/**
 * @Route("/api/v1/adress", name="api_v1_adress")
 */
class AdressController extends AbstractController
{
    /**
     * @Route("/", name="adresslist", methods={"GET"})
     */
    public function list(AdressRepository $ar, SerializerInterface $serializer)
    {
        $adresses = $ar->findAll();
        
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);
        $data = $serializer->normalize($adresses, null, ['groups' => 'api_v1']);
        return $this->json($data);
    }

    /**
     * @Route("/add", name="adressAdd", methods={"POST"})
     */
    public function adressAdd(Request $request, UserRepository $ur)
    {
        $parsed_json = [];
        $content = $request->getContent();
        $parsed_json = json_decode($content, true);

        $line1 = $parsed_json['line1'];
        $line2 = $parsed_json['line2'];
        $city = $parsed_json['city'];
        $cityCode = $parsed_json['cityCode'];
        $userId = $parsed_json['user_id'];
        
        $em = $this->getDoctrine()->getManager();

        $user = $ur->find($userId);

        $adress = new Adress;
        $adress->setLine1($line1)->setLine2($line2)->setCity($city)->setCityCode($cityCode)->setUser($user);
        $em->persist($adress);
        $em->flush();

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);
        $data = $serializer->normalize($adress, null, ['groups' => 'api_v1']);
        return $this->json($data);

    }

    /**
     * @Route("/{id}/update", name="adressUpdate", methods={"PUT"}, requirements={"id":"\d+"})
     */
    public function adressUpdate(int $id, AdressRepository $ar, Request $request)
    {
        $parsed_json = [];
        $content = $request->getContent();
        $parsed_json = json_decode($content, true);

        $line1 = $parsed_json['line1'];
        $line2 = $parsed_json['line2'];
        $city = $parsed_json['city'];
        $cityCode = $parsed_json['cityCode'];
        
        $em = $this->getDoctrine()->getManager();

        $adress = $ar->find($id);
        if (!$adress) {
            throw $this->createNotFoundException(
                'No adress found'
            );
        }
        $adress->setLine1($line1)->setLine2($line2)->setCity($city)->setCityCode($cityCode);
        $em->persist($adress);
        $em->flush();

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);
        $data = $serializer->normalize($adress, null, ['groups' => 'api_v1']);
        return $this->json($data);
    }

    /**
     * @Route("/{id}/delete", name="adressDelete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function adressDelete(int $id, AdressRepository $ar)
    {
        $adress = $ar->find($id);
        $em = $this->getDoctrine()->getManager();

        if (!$adress) {
            throw $this->createNotFoundException('No adress found for id '.$id);
        }
        
        $em->remove($adress);
        $em->flush();

        return $this->json(true);
    }

}
