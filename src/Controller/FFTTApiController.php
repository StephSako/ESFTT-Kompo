<?php

namespace App\Controller;

use App\Repository\EquipeDepartementaleRepository;
use App\Repository\EquipeParisRepository;
use FFTTApi\Exception\InvalidURIParametersException;
use FFTTApi\Exception\NoFFTTResponseException;
use FFTTApi\Exception\URIPartNotValidException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use FFTTApi\FFTTApi;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class FFTTApiController extends AbstractController
{

    private $equipeDepartementaleRepository;
    private $equipeParisRepository;

    /**
     * @param EquipeDepartementaleRepository $equipeDepartementaleRepository
     * @param EquipeParisRepository $equipeParisRepository
     */
    public function __construct(EquipeDepartementaleRepository $equipeDepartementaleRepository, EquipeParisRepository $equipeParisRepository)
    {
        $this->equipeDepartementaleRepository = $equipeDepartementaleRepository;
        $this->equipeParisRepository = $equipeParisRepository;
    }

    /**
     * @param CacheInterface $cache
     * @param $type
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getClassement(CacheInterface $cache, $type){

        if ($type == 'departementale'){
            $equipeDepartementaleRepository = $this->equipeDepartementaleRepository;
            return $cache->get('classementDepartementale', function(ItemInterface $item) use ($equipeDepartementaleRepository) {
                $item->expiresAfter(60 * 60 * 12); // Expire toutes les 12h
                try {
                    $api = new FFTTApi("SW405", "d7ZG56dQKf");
                    $classementE1 = $equipeDepartementaleRepository->find(1)->getLienDivision();
                    $classementE2 = $equipeDepartementaleRepository->find(2)->getLienDivision();
                    $classementE3 = $equipeDepartementaleRepository->find(3)->getLienDivision();
                    $classementE4 = $equipeDepartementaleRepository->find(4)->getLienDivision();

                    return [
                        1 => (!empty($classementE1) ? $api->getClassementPouleByLienDivision($classementE1) : []),
                        2 => (!empty($classementE2) ? $api->getClassementPouleByLienDivision($classementE2) : []),
                        3 => (!empty($classementE3) ? $api->getClassementPouleByLienDivision($classementE3) : []),
                        4 => (!empty($classementE4) ? $api->getClassementPouleByLienDivision($classementE4) : [])
                    ];
                }
                catch (InvalidURIParametersException $e) {  }
                catch (NoFFTTResponseException $e) {  }
                catch (URIPartNotValidException $e) {  }

                return [1 => [], 2 => [], 3 => [], 4 => []];
            });
        }
        else if ($type == 'paris'){
            $equipeParisRepository = $this->equipeParisRepository;
            return $cache->get('classementParis', function(ItemInterface $item) use($equipeParisRepository){
                $item->expiresAfter(60 * 60 * 12); // Expire toutes les 12h
                try {
                    $api = new FFTTApi("SW405", "d7ZG56dQKf");
                    $classementE1 = $equipeParisRepository->find(1)->getLienDivision();
                    $classementE2 = $equipeParisRepository->find(2)->getLienDivision();

                    return [
                        1 => (!empty($classementE1) ? $api->getClassementPouleByLienDivision($classementE1) : []),
                        2 => (!empty($classementE2) ? $api->getClassementPouleByLienDivision($classementE2) : [])
                    ];
                }
                catch (InvalidURIParametersException $e) {  }
                catch (NoFFTTResponseException $e) {  }
                catch (URIPartNotValidException $e) {  }

                return [1 =>  [], 2 => []];
            });
        }
        else return [1 => [], 2 => [], 3 => [], 4 => []];
    }
}