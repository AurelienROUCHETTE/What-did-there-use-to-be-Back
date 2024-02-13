<?php

namespace App\Controller\Api;

use DateTime;
use OA\RequestBody;
use App\Entity\Place;
use App\Entity\Memory;
use DateTimeImmutable;
use App\Entity\Picture;
use App\Entity\Location;
use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use App\Repository\PlaceRepository;
use App\Repository\MemoryRepository;
use App\Repository\PictureRepository;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * This controller groups together all the methods that manage memories.
 * One method displays all memories.
 * One displays only one.
 * Two methods create a memory:
 * -> One creates a memory from an existing locality and creates the name and type of the place if the existing ones are not suitable for this memory.
 * -> Another creates a memory and a new locality as well as the name and type of the corresponding place.
 * One updates a memory with its id by adding, modifying or deleting additional photos.
 * One last deletes a memory by its id and the data assigned to it.
 */
class MemoryController extends AbstractController
{
    /**
     * Display all memories
     * @param MemoryRepository $memoryRepository
     * @return Response
     */
    #[Route('/api/memories', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the memory list',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Memory::class, groups: ['get_memory', 'get_location', 'get_place', 'get_user'])),
            example: [
                [
                    "id" => 1,
                    "title" => "Le Panthéon en 1792",
                    "content" => "Le Panthéon en 1792, avec La Renommée en son sommet.n",
                    "picture_date" => "1792-01-01T00:00:00+00:00",
                    "main_picture" => "https =>\/\/upload.wikimedia.org\/wikipedia\/commons\/thumb\/3\/31\/Pierre-Antoine_de_Machy_-_Le_Panth%C3%A9on.jpg\/1280px-Pierre-Antoine_de_Machy_-_Le_Panth%C3%A9on.jpg",
                    "location" => [
                        "id" => 1,
                        "area" => "Île-de-France",
                        "department" => "Paris",
                        "district" => "Quartier latin",
                        "street" => "28 place du Panthéon",
                        "city" => "Paris",
                        "zipcode" => 75005,
                        "latitude" => "48.84619800",
                        "longitude" => "2.34610500"
                    ],
                    "user" => [
                        "id" => 1,
                        "firstname" => "Aurélien",
                        "lastname" => "ROUCHETTE-MARET",
                        "email" => "aurelien.rouchette@orange.fr",
                        "roles" => [
                            "ROLE_USER",
                            "ROLE_ADMIN"
                        ]
                    ],
                    "place" => [
                        "id" => 1,
                        "name" => "Le Panthéon",
                        "type" => "Mausolée"
                    ]
                ],
                [
                    "id" => 2,
                    "title" => "Le Panthéon de nos jours",
                    "content" => "Le Panthéon vu de la tour Montparnasse en 2016.",
                    "picture_date" => "2016-01-01T00:00:00+00:00",
                    "main_picture" => "https =>\/\/upload.wikimedia.org\/wikipedia\/commons\/thumb\/b\/bb\/Panth%C3%A9on_vu_de_la_tour_Montparnasse_en_2016.jpg\/1280px-Panth%C3%A9on_vu_de_la_tour_Montparnasse_en_2016.jpg",
                    "location" => [
                        "id" => 1,
                        "area" => "Île-de-France",
                        "department" => "Paris",
                        "district" => "Quartier latin",
                        "street" => "28 place du Panthéon",
                        "city" => "Paris",
                        "zipcode" => 75005,
                        "latitude" => "48.84619800",
                        "longitude" => "2.34610500"
                    ],
                    "user" => [
                        "id" => 1,
                        "firstname" => "Aurélien",
                        "lastname" => "ROUCHETTE-MARET",
                        "email" => "aurelien.rouchette@orange.fr",
                        "roles" => [
                            "ROLE_USER",
                            "ROLE_ADMIN"
                        ]
                    ],
                    "place" => [
                        "id" => 1,
                        "name" => "Le Panthéon",
                        "type" => "Mausolée"
                    ]
                ],
                ]
    ))]
    #[OA\Tag(name: 'memory')]
    public function index(MemoryRepository $memoryRepository)
    {
        $memories = $memoryRepository->findAll();

        return $this->json($memories, 200, [], ['groups' => ['get_memory', 'get_location', 'get_place', 'get_user']]);
    }

    /**
     * Display a single memory by its id
     * @param Memory $memory
     * @return Response
     */
    #[Route('/api/memory/{id<\d+>}', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single memory',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Memory::class, groups: ['get_memory', 'get_location', 'get_place', 'get_user'])),
            example: [
                [
                    "id" => 1,
                    "title" => "Le Panthéon en 1792",
                    "content" => "Le Panthéon en 1792, avec La Renommée en son sommet.n",
                    "picture_date" => "1792-01-01T00:00:00+00:00",
                    "main_picture" => "https =>\/\/upload.wikimedia.org\/wikipedia\/commons\/thumb\/3\/31\/Pierre-Antoine_de_Machy_-_Le_Panth%C3%A9on.jpg\/1280px-Pierre-Antoine_de_Machy_-_Le_Panth%C3%A9on.jpg",
                    "location" => [
                        "id" => 1,
                        "area" => "Île-de-France",
                        "department" => "Paris",
                        "district" => "Quartier latin",
                        "street" => "28 place du Panthéon",
                        "city" => "Paris",
                        "zipcode" => 75005,
                        "latitude" => "48.84619800",
                        "longitude" => "2.34610500"
                    ],
                    "user" => [
                        "id" => 1,
                        "firstname" => "Aurélien",
                        "lastname" => "ROUCHETTE-MARET",
                        "email" => "aurelien.rouchette@orange.fr",
                        "roles" => [
                            "ROLE_USER",
                            "ROLE_ADMIN"
                        ]
                    ],
                    "place" => [
                        "id" => 1,
                        "name" => "Le Panthéon",
                        "type" => "Mausolée"
                    ]
                ] 
                ]
    ))]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID of the memory",
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'memory')]
    public function read(Memory $memory = null )
    {
        if (!$memory) {
            return $this->json(
                "Erreur : Souvenir inexistant", 404
            );
        }

        return $this->json($memory, 200, [], ['groups' => ['get_memory', 'get_location', 'get_place', 'get_user']]
    );
    }

    /**
     * Create a new memory
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
     */
    #[Route('/api/secure/create/memory', methods: ['POST'])]
    #[OA\Tag(name: 'hidden')]
    public function create(SerializerInterface $serializer, EntityManagerInterface $entityManager, Request $request)
    {
        $memory = $serializer->deserialize($request->getContent(), Memory::class, 'json');

        $entityManager->persist($memory);
        $entityManager->flush();

        return $this->json($memory, 201, []);
    }

   
    /**
     * First method for creating a memory
     * Create a new memory as well as the name and type of place from a location selected on the map
     * ! Or
     * Create a new memory by selecting the name and type of a pre-existing place from a location selected on the map.
     * 
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param LocationRepository $locationRepository
     * @param UserRepository $userRepository
     * @param PlaceRepository $placeRepository
     * @return Response
     * 
     */
    #[Route('/api/secure/create/memory-and-place', methods: ['POST'])]
    #[OA\RequestBody(  
        description: 'Exemple of data to be supplied to create the memory and place',    
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'location', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                        ]),
                        new OA\Property(property: 'place', type: 'object', properties: [
                            new OA\Property(property: 'create_new_place', type: 'boolean', example: true),
                            new OA\Property(property: 'name', type: 'string', example: "l'elysée"),
                            new OA\Property(property: 'type', type: 'string', example: 'bâtiment'),
                        ]),
                        new OA\Property(property: 'memory', type: 'object', properties: [
                            new OA\Property(property: 'title', type: 'string', example: "l'elysée en 1990"),
                            new OA\Property(property: 'content', type: 'string', example: 'que de souvenirs avec ce lieu'),
                            new OA\Property(property: 'picture_date', type: 'string', format: 'date-time', example: '1990-02-08T14:00:00Z'),
                            new OA\Property(property: 'main_picture', type: 'string', example: 'URL'),
                            new OA\Property(property: 'additional_pictures', type: 'array', items: new OA\Items(type: 'string'), example: ['URL_image_1', 'URL_image_2']),
                        ]),
                    ]
                ),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'location', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                        ]),
                        new OA\Property(property: 'place', type: 'object', properties: [
                            new OA\Property(property: 'create_new_place', type: 'boolean', example: false),
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                        ]),
                        new OA\Property(property: 'memory', type: 'object', properties: [
                            new OA\Property(property: 'title', type: 'string', example: "l'elysée en 1990"),
                            new OA\Property(property: 'content', type: 'string', example: 'que de souvenirs avec ce lieu'),
                            new OA\Property(property: 'picture_date', type: 'string', format: 'date-time', example: '1990-02-08T14:00:00Z'),
                            new OA\Property(property: 'main_picture', type: 'string', example: 'URL'),
                            new OA\Property(property: 'additional_pictures', type: 'array', items: new OA\Items(type: 'string'), example: ['URL_image_1', 'URL_image_2']),
                        ]),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: '')]
    #[OA\Tag(name: 'memory')]
    public function createMemoryAndPlace(Request $request, EntityManagerInterface $entityManager, LocationRepository $locationRepository, PlaceRepository $placeRepository)
    {
        $jsonContent = $request->getContent();
        // $jsonContent = {"user":{"id":1},"location":{"id":1},"place":{"create_new_place":true, "id": 1, "name":"l'elysée","type":"batiment"},"memory":{"title":"l'elysée en 1990","content":"que de souvenirs avec ce lieu","picture_date":"1990-02-08T14:00:00Z","main_picture":"URL","additional_pictures":["URL_image_1","URL_image_2"]}}
   
        $jsonContent = trim($jsonContent);
        $data = json_decode($jsonContent, true);
     
        /** @var \App\Entity\User $user */
         $user = $this->getUser();

        $location = $locationRepository->find($data['location']['id']);
        
        $placeData = $data['place'];
        if ($placeData['create_new_place'] == true) {
        $newPlace = (new Place())
            ->setName($placeData['name'])
            ->setType($placeData['type'])
            ->setLocation($location);
        $entityManager->persist($newPlace);
        $entityManager->flush();
        $place = $placeRepository->find($newPlace); 
        }
        else {
            $place = $placeRepository->find($data['place']['id']);
        }
    
        $memoryData = $data['memory'];
        // dd($memory);
        $newMemory = (new Memory())
            ->setTitle($memoryData['title'])
            ->setContent($memoryData['content'])
            ->setPictureDate(new DateTime($memoryData['picture_date']))
            ->setMainPicture($memoryData['main_picture'])
            ->setUser($user)
            ->setLocation($location)
            ->setPlace($place);

        $entityManager->persist($newMemory);
                   

        // additional image management //
         if (isset($memoryData['additional_pictures']) && is_array($memoryData['additional_pictures'])) {
             foreach ($memoryData['additional_pictures'] as $additionalPictureUrl) {
                 $additionalPicture = (new Picture())
                    ->setPicture($additionalPictureUrl)
                    ->setMemory($newMemory);
                 $entityManager->persist($additionalPicture);
             }
        }
               $entityManager->flush();
     
        return $this->json(['message' => 'Souvenir créé'], Response::HTTP_CREATED);
    }

    /**
     * Second method for creating a memory
     * 
     * TODO: Create a new memory including name, type and location
     * 
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @return Response
     * 
     */
    #[Route('/api/secure/create/memory-and-location-and-place', methods: ['POST'])]
    #[OA\RequestBody(  
        description: 'Exemple of data to be supplied to create the memory and place',    
        content: new OA\JsonContent(

                    properties: [
                        new OA\Property(property: 'memory', type: 'object', properties: [
                            new OA\Property(property: 'title', type: 'string', example: "l'elysée en 1990"),
                            new OA\Property(property: 'content', type: 'string', example: 'que de souvenirs avec ce lieu'),
                            new OA\Property(property: 'picture_date', type: 'string', format: 'date-time', example: '1990-02-08T14:00:00Z'),
                            new OA\Property(property: 'main_picture', type: 'string', example: 'URL'),
                            new OA\Property(property: 'additional_pictures', type: 'array', items: new OA\Items(type: 'string'), example: ['URL_image_1', 'URL_image_2']),
                        ]),
                        new OA\Property(property: 'place', type: 'object', properties: [
                            new OA\Property(property: 'name', type: 'string', example: "l'elysée"),
                            new OA\Property(property: 'type', type: 'string', example: 'bâtiment'),
                        ]),
                        new OA\Property(property: 'location', type: 'object', properties: [
                            new OA\Property(property: 'area', type:'string', example:'Île-de-France'),
                            new OA\Property(property: 'department', type:'string', example:'Paris'),
                            new OA\Property(property: 'district', type:'string', example:'Quartier latin',  nullable: true),
                            new OA\Property(property: 'street', type:'string', example:'28 place du Panthéon'),
                            new OA\Property(property: 'city', type:'string', example:'Paris'),
                            new OA\Property(property: 'zipcode', type:'integer', example:75005),
                            new OA\Property(property: 'latitude', type:'string', example:'48.84619800'),
                            new OA\Property(property: 'longitude', type:'string', example:'2.34610500'),
                    ]
                    ),   
    ]))]
    #[OA\Response(
        response: 201,
        description: '')]
    #[OA\Tag(name: 'memory')]
    public function createMemoryAndLocationAndPlace(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $jsonContent = $request->getContent();
        // $jsonContent = {"user":{"id":1},"location":{"area": "xxx", "department": "xxx", "district": "xxx", "street": "xxx rue xxx", "city": "xxx", "zipcode": "00000", "latitude" : "00.000", "longitude": "0.0000"},"place":{"name":"l'elysée","type":"batiment"},"memory":{"title":"l'elysée en 1990","content":"que de souvenirs avec ce lieu","picture_date":"1990-02-08T14:00:00Z","main_picture":"URL","additional_pictures":["URL_image_1","URL_image_2"]}}

        $jsonContent = trim($jsonContent);
        $data = json_decode($jsonContent, true);
        
          /** @var \App\Entity\User $user */
          $user = $this->getUser();

        $locationData = $data['location'];
        $newLocation = (new Location ())
            ->setArea($locationData['area'])
            ->setDepartment($locationData['department'])
            ->setDistrict($locationData['district'])
            ->setStreet($locationData['street'])
            ->setCity($locationData['city'])
            ->setZipcode($locationData['zipcode'])
            ->setLatitude($locationData['latitude'])
            ->setLongitude($locationData['longitude']);
        $entityManager->persist($newLocation);

        $placeData = $data['place'];
        $newPlace = (new Place())
            ->setName($placeData['name'])
            ->setType($placeData['type'])
            ->setLocation($newLocation);
        $entityManager->persist($newPlace);

        $memoryData = $data['memory'];
        // dd($memory);
        $newMemory = (new Memory())
            ->setTitle($memoryData['title'])
            ->setContent($memoryData['content'])
            ->setPictureDate(new DateTime($memoryData['picture_date']))
            ->setMainPicture($memoryData['main_picture'])
            ->setUser($user)
            ->setPlace($newPlace)
            ->setLocation($newLocation);


        $entityManager->persist($newMemory);
                   
        // additional picture management //
         if (isset($memoryData['additional_pictures']) && is_array($memoryData['additional_pictures'])) {
            foreach ($memoryData['additional_pictures'] as $additionalPictureUrl) {
                $additionalPicture = (new Picture())
                    ->setPicture($additionalPictureUrl)
                    ->setMemory($newMemory);
                $entityManager->persist($additionalPicture);
            }
        }
         $entityManager->flush();
        return $this->json(['message' => 'Souvenir créé'], Response::HTTP_CREATED);
    }

    /**
     * Update a memory by its id
     * @param Memory $memory
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/api/secure/update/memory/{id<\d+>}', methods: ['PUT'])]
    #[OA\Tag(name: 'hidden')]
    public function update(Memory $memory = null, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        if(!$memory) {
            return $this->json(
                "Erreur : Le souvenir n'existe pas", 404
            );
        }
        $serializer->deserialize($request->getContent(), Memory::class, 'json', ['object_to_populate'=>$memory]);
        $memory->setUpdatedAt(new DateTimeImmutable());

        $entityManager->flush();

        return $this->json($memory, 200, [], ['groups' => ['get_memory']]);
    }

    /**
     * TODO : Update a memory by its id
     * 
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PlaceRepository $placeRepository
     * @param MemoryRepository $memoryRepository
     * @param PictureRepository $pictureRepository
     * @return Response
     * 
     */
    #[Route('/api/secure/update/memory-and-place/{id<\d+>}', methods: ['PUT'])]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID of the memory",
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(  
        description: 'Example of data to be supplied to update the memory and place',    
        content: new OA\JsonContent(
            oneOf: [
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'place',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'update_place', type: 'boolean', example: true),
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'new name'),
                                new OA\Property(property: 'type', type: 'string', example: 'new type'),
                            ]
                        ),
                        new OA\Property(
                            property: 'memory',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 7),
                                new OA\Property(property: 'title', type: 'string', example: 'new title'),
                                new OA\Property(property: 'content', type: 'string', example: 'new content'),
                                new OA\Property(property: 'picture_date', type: 'string', format: 'date-time', example: '1890-02-08T14:00:00Z'),
                                new OA\Property(property: 'main_picture', type: 'string', example: 'nouvelle_URL'),
                                new OA\Property(
                                    property: 'additional_pictures',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 7),
                                            new OA\Property(property: 'URL_image', type: 'string', example: 'nouvelle_URL_image_12'),
                                        ]
                                    ),
                                    example: [
                                        ['id' => 7, 'URL_image' => 'nouvelle_URL_image_12'],
                                        ['id' => 2, 'URL_image' => 'nouvelle_URL_image_24'],
                                        ['URL_image' => 'nouvelle_URL_image_38'],
                                    ],
                                ),
                            ]
                        ),
                    ]
                ),
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'place',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'update_place', type: 'boolean', example: false),
                            ]
                        ),
                        new OA\Property(
                            property: 'memory',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 7),
                                new OA\Property(property: 'title', type: 'string', example: 'l\'elysée en 1990'),
                                new OA\Property(property: 'content', type: 'string', example: 'que de souvenirs avec ce lieu'),
                                new OA\Property(property: 'picture_date', type: 'string', format: 'date-time', example: '1990-02-08T14:00:00Z'),
                                new OA\Property(property: 'main_picture', type: 'string', example: 'URL'),
                                new OA\Property(
                                    property: 'additional_pictures',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['URL_image_1', 'URL_image_2'],
                                ),
                            ]
                        ),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: '')]
    #[OA\Tag(name: 'memory')]
    public function updateMemoryAndPlace(Request $request, EntityManagerInterface $entityManager, PlaceRepository $placeRepository, MemoryRepository $memoryRepository, PictureRepository $pictureRepository)
    {
        $jsonContent = $request->getContent();
        // $jsonContent ={"place":{"update_place":true,"id":1,"name":"nouveau_nom","type":"nouveau_type"},"memory":{"id":1,"title":"nouveau_titre","content":"nouveau_contenu","picture_date":"1890-02-08T14:00:00Z","main_picture":"nouvelle_URL","additional_pictures":[{"id":1,"URL_image":"nouvelle_URL_image_12"},{"id":2,"URL_image":"nouvelle_URL_image_24"},{"URL_image":"nouvelle_URL_image_38"}]}}

   
        $jsonContent = trim($jsonContent);
        $data = json_decode($jsonContent, true);
           
        $placeData = $data['place'];
        if ($placeData['update_place'] == true) {
        $currentPlace = $placeRepository->find($data['place']['id'])
            ->setName($placeData['name'])
            ->setType($placeData['type'])
            ->setUpdatedAt(new DateTimeImmutable());
        $entityManager->persist($currentPlace);
        $entityManager->flush();
        }
        
    
        $memoryData = $data['memory'];
        $currentMemory = $memoryRepository->find($data['memory']['id'])
            ->setTitle($memoryData['title'])
            ->setContent($memoryData['content'])
            ->setPictureDate(new DateTime($memoryData['picture_date']))
            ->setMainPicture($memoryData['main_picture'])
            ->setUpdatedAt(new DateTimeImmutable());
            // ->setPlace($place);

        $entityManager->persist($currentMemory);
                   

        // additional picture management //
        foreach ($memoryData['additional_pictures'] as $additionalPictureData) {
            $additionalPictureId = isset($additionalPictureData['id']) ? $additionalPictureData['id'] : null;
        
            if ($additionalPictureId) {
                // Update an existing picture
                $additionalPicture = $pictureRepository->find($additionalPictureId);
                if ($additionalPicture) {
                    $additionalPicture
                        ->setPicture($additionalPictureData['URL_image'])
                        ->setMemory($currentMemory)
                        ->setUpdatedAt(new DateTimeImmutable());
        
                    $entityManager->persist($additionalPicture);
                }
            } else {
                // Create a new picture
                $newAdditionalPicture = new Picture();
                $newAdditionalPicture
                    ->setPicture($additionalPictureData['URL_image'])
                    ->setMemory($currentMemory);
        
                $entityManager->persist($newAdditionalPicture);
            }
        }
               $entityManager->flush();
        return $this->json(['message' => 'Souvenir mis à jour'], Response::HTTP_OK);
    }

    /**
     * Delete a memory by its id
     */
    #[Route('/api/secure/delete/memory/{id<\d+>}', methods: ['DELETE'])]
    #[OA\Response(
        response: 200,
        description: 'Deletes a memory',
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID of the memory",
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'memory')]
    public function delete(Memory $memory, EntityManagerInterface $entityManager): Response
    {
        if(!$memory) {
            return $this->json(
                "Erreur : Le souvenir n'existe pas", 404
            );
        }
        $entityManager->remove($memory);
        $entityManager->flush();

        return $this->json(['message' => 'Souvenir supprimé'], Response::HTTP_OK);
    }
}