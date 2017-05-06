<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Game controller.
 *
 * @Route("game")
 */
class GameController extends Controller
{
    /**
     * Lists all game entities.
     *
     * @Rest\View()
     * @Rest\Get("")
     * @Rest\QueryParam(name="status", requirements="0|1",strict=true, nullable=true, description="availability")
     */
    public function getAllGamesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $status = $request->query->get('status');

        if ($status !== null){
            if ($status !== "0" && $status !== "1"){
                $msg = "status parameter must be 1(allowed) or 0(not allowed)";
                $this->get('logger')->err($msg);
                return new JsonResponse($msg, Response::HTTP_BAD_REQUEST);
            }
            $games = $em->getRepository('AppBundle:Game')->findByStatus($status);
        }
        else {
            $games = $em->getRepository('AppBundle:Game')->findAll();
        }

        return $games;
    }

    /**
     * Detail a game entity.
     *
     * @Rest\View()
     * @Rest\Get("/{id}", requirements={"id": "\d+"})
     * @ParamConverter("game", class="AppBundle:Game")
     */
    public function getOneGamesAction(Request $request, Game $game)
    {
        return $game;
    }

    /**
     * Creates a new game entity.
     *
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("", name="game_create")
     * @ParamConverter("game", converter="fos_rest.request_body")
     * @Rest\RequestParam(name="title", nullable=false, description="title")
     * @Rest\RequestParam(name="href", default="", description="link to the game")
     * @Rest\RequestParam(name="img", default="", description="description image")
     * @Rest\RequestParam(name="status", requirements="0|1", nullable=false, description="availability")
     */
    public function createAction(Request $request, Game $game)
    {
        $em = $this->getDoctrine()->getManager();

        $sav = $em->getRepository('AppBundle:Game')->findByTitle($game->getTitle());
        if ($sav){
            $msg = "This title already exist";
            $this->get('logger')->err($msg);
            return new JsonResponse($msg, Response::HTTP_BAD_REQUEST);
        }
        if(!$game->getTitle()){
           return new JsonResponse("NULL title is not allowed", Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($game);
        $em->flush();

        return $game;
    }


    /**
     * Edit an existing game entity.
     *
     * @Rest\Put("/{id}", requirements={"id": "\d+"})
     * @Rest\View()
     * @ParamConverter("game", class="AppBundle:Game")
     * @Rest\RequestParam(name="title", nullable=false, description="title")
     * @param Request $request
     * @param Game $game
     * @return Game|object
     */
    public function editAction(Request $request, Game $game)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $em = $this->getDoctrine()->getManager();

        $game =  $serializer->denormalize($request->request->all(), '\AppBundle\Entity\Game', null, array('object_to_populate' => $game));
        $sav = $em->getRepository('AppBundle:Game')->findByTitle($game->getTitle());
        if ($sav){
            $msg = "This title already exist";
            $$this->get('logger')->err($msg);
            return new JsonResponse($msg, Response::HTTP_BAD_REQUEST);
        }
        if(!$game->getTitle()){
            $msg = "Empty title is not allowed";
            $this->get('logger')->err($msg);
            return new JsonResponse($msg, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($game);
        $em->flush();

        return $game;
    }

    /**
     * Deletes a game entity.
     *
     * @Rest\Delete("/{id}", requirements={"id": "\d+"})
     * @Rest\View(statusCode=204)
     * @ParamConverter("game", class="AppBundle:Game", options={"catchError"={"message"="Game not found"}})
     * @param Request $request
     * @param Game $game
     * @return Game
     */
    public function deleteAction(Request $request, Game $game)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($game);
        $em->flush();

        return $game;
    }

}
