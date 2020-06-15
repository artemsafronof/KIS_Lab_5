<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConversationController extends AbstractController
{
    public function getUserConversations($userId)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);
        if (!$user){
            return new Response('Пользователь не найден');
        }
        $conversations = $user->getConversations();
        if (!$conversations) return new Response('У пользователя нет бесед');
        foreach($conversations as $conversation) {
            $convers[] = array(
                'id' => $conversation->getId(),
                'title' => $conversation->getTitle(),
                'description' => $conversation->getDescription(),
                'status' => $conversation->getStatus(),
            );
        }
        return new JsonResponse($convers);
    }

    public function getConversation($id)
    {
        $conversation = $this->getDoctrine()
            ->getRepository(Conversation::class)
            ->find($id);
        if (!$conversation){
            return new Response('Беседа не найдена');
        }
        $conversationJSON = [
            'id' => $conversation->getId(),
            'title' => $conversation->getTitle(),
            'description' => $conversation->getDescription(),
            'status' => $conversation->getStatus(),
        ];
        return new JsonResponse($conversationJSON);
    }

    public function createConversation (Request $request): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $conversation = new Conversation();
        $conversation->setTitle($request->request->get('title'));
        $conversation->setDescription($request->request->get('description'));
        $conversation->setStatus($request->request->get('status'));
        $userId = $request->request->get('userId');
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);
        $conversation->addParticipant($user);
        $entityManager->persist($conversation);
        $entityManager->flush();
        return new Response('Беседа успешно добавлена, идентификатор: '.$conversation->getId());
    }

    public function patchConversation ($id, Request $request): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $conversation = $this->getDoctrine()
            ->getRepository(Conversation::class)
            ->find($id);
        if (!$conversation) {
            return new Response('Беседа не существует');
        } else {
            $conversation->setTitle($request->request->get('title'));
            $conversation->setDescription($request->request->get('description'));
            $conversation->setStatus($request->request->get('status'));
            $entityManager->persist($conversation);
            $entityManager->flush();
            return new Response('Беседа была успешно изменена, идентификатор: ' . $conversation->getId());
        }
    }

    public function deleteConversation($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $conversation = $entityManager->getRepository(Conversation::class)->find($id);
        if (!$conversation) return new Response('Беседа не найдена');
        $entityManager->remove($conversation);
        $entityManager->flush();
        return new Response('Беседа с идентификатором '.$id.' была успешно удалена');
    }
}
