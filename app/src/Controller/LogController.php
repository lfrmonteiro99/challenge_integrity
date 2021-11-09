<?php

namespace App\Controller;

use App\Entity\Log;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController
{
    private $params;
    private $entityManager;

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $entityManager){
        $this->params = $params;
        $this->entityManager = $entityManager;
    }
    /*
     * @Route("/")
     */
    public function index()
    {
        $logs = $this->getDoctrine()
            ->getRepository(Log::class)
            ->findAll();

        foreach($logs as &$log){
            $log->params = explode('&', $log->getParameters());
            $log->response = $log->getResult() ? 'Successful' : 'Not successful';
        }

        $rootImages = $this->params->get("uploads_directory");

        return $this->render('log/index.html.twig', [
            'controller_name' => 'LogController',
            'logs' => $logs,
            'rootImages' => $rootImages
        ]);
    }

    public function store($input, $queryParams, $fileName = NULL, $result = false){
        try{
            $log = new Log();
            $log->setUrl($input->getOption('url') ?? '');
            $log->setResult($result);
            $log->setParameters($queryParams);
            $log->setImage($fileName);
            $log->setCreatedAt(new \DateTime("now"));
            $this->entityManager->persist($log);
            $this->entityManager->flush();
            return true;
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function show($id)
    {
        $log = $this->getDoctrine()
            ->getRepository(Log::class)
            ->find($id);

        return $this->json($log->getImage());
    }


}
