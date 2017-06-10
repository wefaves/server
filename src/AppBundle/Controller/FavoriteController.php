<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use AppBundle\Entity\Favorite;

class FavoriteController extends Controller {

    /**
     *
     * Get the whole favorite of the user.
     *
     * ### Response ###
     *
     *      {
     *          "id": 1,
     *          "indexId": 42,
     *          "title": "wefaves",
     *          "url": "https://monportail.ulaval.ca/accueil/",
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
     * @Rest\View(serializerGroups={"favorite"})
     * @Rest\Get("/users/self/favorite")
     *
     * @ApiDoc(
     *     description = "Get the whole favorite of the user",
     *     section = "Favorite",
     *     views = { "default", "favorite" },
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
     *         "beta" = "#0066ff"
     *     }
     * )
     */
    public function getFavoritesAction(Request $request) {

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

        $favorite = $user->getFavorites();

        return $favorite;
    }

    /**
     *
     * Get a single favorite.
     *
     * ### Response ###
     *
     *      {
     *          "id": 1,
     *          "indexId": 42,
     *          "title": "wefaves",
     *          "url": "https://monportail.ulaval.ca/accueil/",
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
     * @Rest\View(serializerGroups={"favorite"})
     * @Rest\Get("/users/self/favorite/{id}")
     *
     * @ApiDoc(
     *      description = "Get a single favorite",
     *      section = "Favorite",
     *      views = { "default", "favorite" },
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
     *         404 = "Returned when favorite was not found"
     *      },
     *      tags = {
     *         "beta" = "#0066ff"
     *      }
     * )
     */
    public function getFavoriteAction(Request $request) {

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

        $repository = $this->getDoctrine()->getRepository('AppBundle:favorite');
        $favorite = $repository->find($request->get("id"));

        if (empty($favorite)) {
            return new JsonResponse(array("message" => "The requested favorite was not found"), Response::HTTP_NOT_FOUND);
        }

        if ($favorite->getUser()->getId() != $user->getId()) {
            return new JsonResponse(array("message" => "You are not allowed to access to the requested resource"), Response::HTTP_UNAUTHORIZED);
        }

        return $favorite;
    }

    /**
     *
     * Post a favorite.
     *
     * ### Example request ###
     *
     *      {
     *          "indexId": "1063",
     *          "title": "wefaves",
     *          "url": "https://monportail.ulaval.ca/accueil/"
     *      }
     *
     * ### Response ###
     *
     *      {
     *          "id": 1,
     *          "indexId": 42,
     *          "title": "wefaves",
     *          "url": "https://monportail.ulaval.ca/accueil/",
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
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"favorite"})
     * @Rest\Post("/users/self/favorite")
     *
     * @ApiDoc(
     *      description = "Post a favorite",
     *      section = "Favorite",
     *      views = { "default", "favorite" },
     *      headers = {
     *          {
     *              "name" = "Authorization",
     *              "description" = "Authorization token. Value look like this: Bearer {token}",
     *              "required" = "true"
     *          }
     *      },
     *     parameters = {
     *          { "name" = "indexId", "required" = "true", "dataType" = "String", "description" = "Identifier of the favorite (id)" },
     *          { "name" = "title", "required" = "true", "dataType" = "String", "description" = "Title of the favorite" },
     *          { "name" = "url", "required" = "true", "dataType" = "String", "description" = "Url of the favorite" }
     *     },
     *      statusCodes = {
     *         201 = "Returned when favorite is created",
     *         401 = "Returned when user is not authenticated",
     *         400 = "Returned on bad request"
     *      },
     *      tags = {
     *         "beta" = "#0066ff"
     *      }
     * )
     *
     * @TODO FORM Validation
     */
    public function postFavoriteAction(Request $request) {

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

        $favorite = new Favorite();

        $favorite->setIndexId(intval($request->get("indexId")));
        $favorite->setTitle($request->get("title"));
        $favorite->setUrl($request->get("url"));

        $user->addFavorite($favorite);

        $em = $this->getDoctrine()->getManager();

        $em->persist($favorite);
        $em->flush();

        return $favorite;
    }

    /**
     *
     * Update a favorite.
     *
     * ### Response ###
     *
     *      {
     *          "data": {
     *              "indexId": "1063",
     *              "title": "wefaves",
     *              "url": "https://monportail.ulaval.ca/accueil/"
     *          }
     *      }
     *
     * @Rest\View()
     * @Rest\Patch("/users/self/favorite/{id}")
     *
     * @ApiDoc(
     *      description = "Update a favorite",
     *      section = "Favorite",
     *      views = { "default", "favorite" },
     *      headers = {
     *          {
     *              "name" = "Authorization",
     *              "description" = "Authorization token. Value look like this: Bearer {token}",
     *              "required" = "true"
     *          }
     *      },
     *      requirements = {
     *          { "name" = "id", "requirement" = "\d+", "dataType" = "Integer", "description" = "Unique id of a favorite" }
     *      },
     *      statusCodes = {
     *         200 = "Returned when successful",
     *         401 = "Returned when user is not authenticated",
     *         404 = "Returned when favorite was not found"
     *      },
     *      tags = {
     *         "in-development" = "#cc0066"
     *      }
     * )
     */
    public function patchFavoriteAction(Request $request) {

        return true;
    }

    /**
     *
     * Delete a favorite.
     *
     * @Rest\View()
     * @Rest\Delete("/users/self/favorite/{id}")
     *
     * @ApiDoc(
     *      description = "Delete a favorite",
     *      section = "Favorite",
     *      views = { "default", "favorite" },
     *      headers = {
     *          {
     *              "name" = "Authorization",
     *              "description" = "Authorization token. Value look like this: Bearer {token}",
     *              "required" = "true"
     *          }
     *      },
     *      requirements = {
     *          { "name" = "id", "requirement" = "\d+", "dataType" = "Integer", "description" = "Unique id of a favorite" }
     *      },
     *      statusCodes = {
     *         200 = "Returned when successful",
     *         401 = "Returned when user is not authenticated",
     *         404 = "Returned when favorite was not found"
     *      },
     *      tags = {
     *         "beta" = "#0066ff"
     *      }
     * )
     */
    public function deleteFavoriteAction(Request $request) {

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

        $repository = $this->getDoctrine()->getRepository('AppBundle:Favorite');
        $favorite = $repository->find($request->get("id"));

        if (empty($favorite)) {
            return new JsonResponse(array("message" => "The requested favorite was not found"), Response::HTTP_NOT_FOUND);
        }

        if ($favorite->getUser()->getId() != $user->getId()) {
            return new JsonResponse(array("message" => "You must be the owner of this favorite to delete"), Response::HTTP_UNAUTHORIZED);
        }

        $user->removeFavorite($favorite);

        $em = $this->getDoctrine()->getManager();
        $em->remove($favorite);
        $em->flush();

        return  new JsonResponse(array("message" => "favorite delete"), Response::HTTP_UNAUTHORIZED);
    }
}