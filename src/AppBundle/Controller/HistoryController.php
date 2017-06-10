<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use AppBundle\Entity\History;

class HistoryController extends Controller {

    /**
     *
     * Get the whole history of the user.
     *
     * ### Response ###
     *
     *      {
     *          "id": 1,
     *          "title": "wefaves",
     *          "lastVisit": "1480984181505.387",
     *          "typedCount": 0,
     *          "url": "https://monportail.ulaval.ca/accueil/",
     *          "visitCount": 1063,
     *          "user": {
     *              "id": 10,
     *              "username": "ju",
     *              "usernameCanonical": "ju",
     *              "email": "gh@gmail.com",
     *              "emailCanonical": "gh@gmail.com",
     *              "lastLogin": "2016-12-08T08:02:15+00:00",
     *              "roles": [
     *                  "ROLE_USER"
     *              ]
     *          }
     *      }
     *
     * @Rest\View(serializerGroups={"history"})
     * @Rest\Get("/users/self/history")
     *
     * @ApiDoc(
     *     description = "Get the whole history of the user",
     *     section = "History",
     *     views = { "default", "history" },
     *     headers = {
     *          {
     *              "name" = "Authorization",
     *              "description" = "Authorization token. Value look like this: Bearer {token}",
     *              "required" = "true"
     *          }
     *     },
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         401 = "Returned when user is not authenticated"
     *     },
     *     tags = {
     *         "stable" = "#33cc33"
     *     }
     * )
     */
    public function getHistoriesAction(Request $request) {

        if (empty($request->server->get("HTTP_AUTHORIZATION"))) {
            return new JsonResponse(array("message" => "You must be authenticated"), Response::HTTP_UNAUTHORIZED);
        }

        /**
         *
         * @TODO DEFINE AS A SERVICE
         *
         */
        $userManager = $this->get('fos_user.user_manager');

        $auth_header = explode(' ', $request->server->get("HTTP_AUTHORIZATION"));
        $token = $auth_header[1];

        $data = $this->get('lexik_jwt_authentication.encoder')->decode($token);

        $user = $userManager->findUserBy(array("id" => $data["id"]));

        $history = $user->getHistories();

        return $history;
    }

    /**
     *
     * Get a single history.
     *
     * ### Response ###
     *
     *      {
     *          "id": 1,
     *          "title": "wefaves",
     *          "lastVisit": "1480984181505.387",
     *          "typedCount": 0,
     *          "url": "https://monportail.ulaval.ca/accueil/",
     *          "visitCount": 1063,
     *          "user": {
     *              "id": 10,
     *              "username": "ju",
     *              "usernameCanonical": "ju",
     *              "email": "gh@gmail.com",
     *              "emailCanonical": "gh@gmail.com",
     *              "lastLogin": "2016-12-08T08:02:15+00:00",
     *              "roles": [
     *                  "ROLE_USER"
     *              ]
     *          }
     *      }
     *
     * @Rest\View(serializerGroups={"history"})
     * @Rest\Get("/users/self/history/{id}")
     *
     * @ApiDoc(
     *      description = "Get a single history",
     *      section = "History",
     *      views = { "default", "history" },
     *      headers = {
     *          {
     *              "name" = "Authorization",
     *              "description" = "Authorization token. Value look like this: Bearer {token}",
     *              "required" = "true"
     *          }
     *      },
     *      requirements = {
     *          { "name" = "id", "requirement" = "\d+", "dataType" = "Integer", "description" = "Unique id of user" }
     *      },
     *      statusCodes = {
     *         200 = "Returned when successful",
     *         401 = "Returned when user is not authenticated",
     *         404 = "Returned when history was not found"
     *      },
     *      tags = {
     *         "stable" = "#33cc33"
     *      }
     * )
     */
    public function getHistoryAction(Request $request) {

        if (empty($request->server->get("HTTP_AUTHORIZATION"))) {
            return new JsonResponse(array("message" => "You must be authenticated"), Response::HTTP_UNAUTHORIZED);
        }

        /**
         *
         * @TODO DEFINE AS A SERVICE
         *
         */
        $userManager = $this->get('fos_user.user_manager');

        $auth_header = explode(' ', $request->server->get("HTTP_AUTHORIZATION"));
        $token = $auth_header[1];

        $data = $this->get('lexik_jwt_authentication.encoder')->decode($token);

        $user = $userManager->findUserBy(array("id" => $data["id"]));

        $repository = $this->getDoctrine()->getRepository('AppBundle:History');
        $history = $repository->find($request->get("id"));

        if (empty($history)) {
            return new JsonResponse(array("message" => "The requested history was not found"), Response::HTTP_NOT_FOUND);
        }

        if ($history->getUser()->getId() != $user->getId()) {
            return new JsonResponse(array("message" => "You are not allowed to access to the requested resource"), Response::HTTP_UNAUTHORIZED);
        }

        return $history;
    }

    /**
     *
     * Post a history.
     *
     * ### Example request ###
     *
     *      {
     *          "title": "wefaves",
     *          "lastVisit": "1480984181505.387",
     *          "typedCount": "0"
     *          "url": "https://monportail.ulaval.ca/accueil/"
     *          "visitCount": "1063"
     *      }
     *
     * ### Response ###
     *
     *      {
     *          "id": 1,
     *          "title": "wefaves",
     *          "lastVisit": "1480984181505.387",
     *          "typedCount": 0,
     *          "url": "https://monportail.ulaval.ca/accueil/",
     *          "visitCount": 1063,
     *          "user": {
     *              "id": 10,
     *              "username": "ju",
     *              "usernameCanonical": "ju",
     *              "email": "gh@gmail.com",
     *              "emailCanonical": "gh@gmail.com",
     *              "lastLogin": "2016-12-08T08:02:15+00:00",
     *              "roles": [
     *                  "ROLE_USER"
     *              ]
     *          }
     *      }
     *
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"history"})
     * @Rest\Post("/users/self/history")
     *
     * @ApiDoc(
     *      description = "Post a history",
     *      section = "History",
     *      views = { "default", "history" },
     *      headers = {
     *          {
     *              "name" = "Authorization",
     *              "description" = "Authorization token. Value look like this: Bearer {token}",
     *              "required" = "true"
     *          }
     *      },
     *     parameters = {
     *          { "name" = "title", "required" = "true", "dataType" = "String", "description" = "Title of the history" },
     *          { "name" = "lastVisit", "required" = "true", "dataType" = "String", "description" = "Last time of visit the history on browser" },
     *          { "name" = "typedCount", "required" = "true", "dataType" = "String", "description" = "" },
     *          { "name" = "url", "required" = "true", "dataType" = "String", "description" = "Url of the history" },
     *          { "name" = "visitCount", "required" = "true", "dataType" = "String", "description" = "Number's visit of the history" }
     *     },
     *      statusCodes = {
     *         201 = "Returned when history is created",
     *         401 = "Returned when user is not authenticated",
     *         400 = "Returned on bad request"
     *      },
     *      tags = {
     *         "stable" = "#33cc33"
     *      }
     * )
     *
     * @TODO FORM Validation
     */
    public function postHistoryAction(Request $request) {

        if (empty($request->server->get("HTTP_AUTHORIZATION"))) {
            return new JsonResponse(array("message" => "You must be authenticated"), Response::HTTP_UNAUTHORIZED);
        }

        /**
         *
         * @TODO DEFINE AS A SERVICE
         *
         */
        $userManager = $this->get('fos_user.user_manager');

        $auth_header = explode(' ', $request->server->get("HTTP_AUTHORIZATION"));
        $token = $auth_header[1];

        $data = $this->get('lexik_jwt_authentication.encoder')->decode($token);

        $user = $userManager->findUserBy(array("id" => $data["id"]));

        $history = new History();

        $history->setTitle($request->get("title"));
        $history->setTypedCount(intval($request->get("typeCount")));
        $history->setUrl($request->get("url"));
        $history->setLastVisit(floatval($request->get("lastVisit")));
        $history->setVisitCount(intval($request->get("visitCount")));
        $history->setUpdatedAt(new \DateTime());
        $history->setCreatedAt(new \DateTime());

        $user->addHistory($history);

        $em = $this->getDoctrine()->getManager();

        $em->persist($history);
        $em->flush();

        return $history;
    }

    /**
     *
     * Update a history.
     *
     * ### Response ###
     *
     *      {
     *          "data": {
     *              "id": 10,
     *              "title": "wefaves",
     *              "lastVisit": "1480984181505.387",
     *              "typedCount": "0",
     *              "url": "https://monportail.ulaval.ca/accueil/",
     *              "visitCount": "1063"
     *          }
     *      }
     *
     * @Rest\View()
     * @Rest\Patch("/users/self/history/{id}")
     *
     * @ApiDoc(
     *      description = "Update a history",
     *      section = "History",
     *      views = { "default", "history" },
     *      headers = {
     *          {
     *              "name" = "Authorization",
     *              "description" = "Authorization token. Value look like this: Bearer {token}",
     *              "required" = "true"
     *          }
     *      },
     *      requirements = {
     *          { "name" = "id", "requirement" = "\d+", "dataType" = "Integer", "description" = "Unique id of a history" }
     *      },
     *      statusCodes = {
     *         200 = "Returned when successful",
     *         401 = "Returned when user is not authenticated",
     *         404 = "Returned when history was not found"
     *      },
     *      tags = {
     *         "in-development" = "#cc0066"
     *      }
     * )
     */
    public function patchHistoryAction(Request $request) {

        return true;
    }

    /**
     *
     * Delete a history.
     *
     * @Rest\View()
     * @Rest\Delete("/users/self/history/{id}")
     *
     * @ApiDoc(
     *      description = "Delete a history",
     *      section = "History",
     *      views = { "default", "history" },
     *      headers = {
     *          {
     *              "name" = "Authorization",
     *              "description" = "Authorization token. Value look like this: Bearer {token}",
     *              "required" = "true"
     *          }
     *      },
     *      requirements = {
     *          { "name" = "id", "requirement" = "\d+", "dataType" = "Integer", "description" = "Unique id of a history" }
     *      },
     *      statusCodes = {
     *         200 = "Returned when successful",
     *         401 = "Returned when user is not authenticated",
     *         404 = "Returned when history was not found"
     *      },
     *      tags = {
     *         "stable" = "#33cc33"
     *      }
     * )
     */
    public function deleteHistoryAction(Request $request) {

        if (empty($request->server->get("HTTP_AUTHORIZATION"))) {
            return new JsonResponse(array("message" => "You must be authenticated"), Response::HTTP_UNAUTHORIZED);
        }

        /**
         *
         * @TODO DEFINE AS A SERVICE
         *
         */
        $userManager = $this->get('fos_user.user_manager');

        $auth_header = explode(' ', $request->server->get("HTTP_AUTHORIZATION"));
        $token = $auth_header[1];

        $data = $this->get('lexik_jwt_authentication.encoder')->decode($token);

        $user = $userManager->findUserBy(array("id" => $data["id"]));

        $repository = $this->getDoctrine()->getRepository('AppBundle:History');
        $history = $repository->find($request->get("id"));

        if (empty($history)) {
            return new JsonResponse(array("message" => "The requested history was not found"), Response::HTTP_NOT_FOUND);
        }

        if ($history->getUser()->getId() != $user->getId()) {
            return new JsonResponse(array("message" => "You must be the owner of this history to delete"), Response::HTTP_UNAUTHORIZED);
        }

        $user->removeHistory($history);

        $em = $this->getDoctrine()->getManager();
        $em->remove($history);
        $em->flush();

        return  new JsonResponse(array("message" => "History delete"), Response::HTTP_UNAUTHORIZED);
    }
}