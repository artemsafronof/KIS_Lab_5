<?php


namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use \Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends AbstractController
{
    /**
     * @param $userId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getUserMessages ($userId) {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);
        if (!$user){
            return new Response('Пользователь не найден');
        }
        $messages = $user->getMessages();
        if (!$messages) return new Response('У пользователя нет сообщений');
        foreach($messages as $message) {
            $mess[] = array(
                'id' => $message->getId(),
                'sender' => $message->getSender()->getId(),
                'conversation' => $message->getConversation()->getId(),
                'text' => $message->getText(),
                'status' => $message->getStatus(),
            );
        }
        return new JsonResponse($mess);
    }

    public function getMessage ($id) {
        $message = $this->getDoctrine()
            ->getRepository(Message::class)
            ->find($id);
        if (!$message){
            return new Response('Сообщение не найдено');
        }
        $messageJSON = [
            'id' => $message->getId(),
            'sender' => $message->getSender()->getId(),
            'conversation' => $message->getConversation()->getId(),
            'text' => $message->getText(),
            'status' => $message->getStatus(),
        ];
        return new JsonResponse($messageJSON);
    }

    public function createMessage (Request $request): Response {
        $entityManager = $this->getDoctrine()->getManager();
        $message = new Message();
        $message->setText($request->request->get('text'));
        $message->setStatus($request->request->get('status'));
        $userId = $request->request->get('userId');
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);
        $message->setSender($user);
        $conversationId = $request->request->get('conversationId');
        $conversation = $this->getDoctrine()
            ->getRepository(Conversation::class)
            ->find($conversationId);
        $message->setConversation($conversation);
        $entityManager->persist($message);
        $entityManager->flush();
        return new Response('Сообщение успешно добавлено, идентификатор: '.$message->getId());
    }

    public function patchMessage ($id, Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $message = $this->getDoctrine()
            ->getRepository(Message::class)
            ->find($id);
        if (!$message) {
            return new Response('Сообщение не найдено');
        } else {
            $message->setText($request->request->get('text'));
            $message->setStatus($request->request->get('status'));
            $userId = $request->request->get('userId');
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($userId);
            $message->setSender($user);
            $conversationId = $request->request->get('conversationId');
            $conversation = $this->getDoctrine()
                ->getRepository(Conversation::class)
                ->find($conversationId);
            $message->setConversation($conversation);
            $entityManager->persist($message);
            $entityManager->flush();
            return new Response('Сообщение успешно изменено, идентификатор: '.$message->getId());
        }
    }

    public function deleteMessage ($id) {
        $entityManager = $this->getDoctrine()->getManager();
        $message = $entityManager->getRepository(Message::class)->find($id);
        if (!$message) return new Response('Сообщение не найдено');
        $entityManager->remove($message);
        $entityManager->flush();
        return new Response('Сообщение с идентификатором '.$id.' было успешно удалено');
    }
}
