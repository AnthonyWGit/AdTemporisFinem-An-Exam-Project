<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Suggestion;
use App\Form\SuggestionType;
use App\Service\FileUploader;
use App\Repository\PlayerRepository;
use App\Repository\SuggestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CommunityController extends AbstractController
{
    #[Route('/community', name: 'community')]
    public function index(SuggestionRepository $suggestionRepository, PlayerRepository $playerRepository): Response
    {
        $suggestions = $suggestionRepository->findSuggestionsOrderedByLikes();
        return $this->render('community/index.html.twig', [
            'suggestions' => $suggestions
        ]);
    }

    #[Route('community/suggestion/new', name: 'newSuggestion')]
    #[Route('community/suggestion/{id}/edit', name: 'editSuggestion')]
    public function new(Suggestion $suggestion = null, FileUploader $fileUploader = null, Request $request, EntityManagerInterface $entityManager): Response
    {
        //Banned users should not be able to post a suggestion or edit the one they already made 
        // Get the current user
        $user = $this->getUser();

        // Check if the user has the "ROLE_BANNED"
        if ($this->isGranted('ROLE_BANNED', $user)) {
            // If the user has the "ROLE_BANNED", throw an AccessDeniedException
            throw new AccessDeniedException('You have been banned. Check your account.');
        }

        foreach ($this->getUser()->getSuggestions() as $suggestion)
        {
            if ($suggestion->getStatus("pending"))
            {
                $this->addFlash // need to be logged as user to see the flash messages build-in Symfony
                (
                    'notice',
                    'You already have made a suggestion !'
                );
                return $this->redirectToRoute('community');
            }
            else
            {

            }
        }

        // creates a task object and initializes some data for this example
        if ($suggestion === null) {
            $suggestion = new Suggestion();
        }

        $form = $this->createForm(SuggestionType::class, $suggestion);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) 
            {

                $date = new \DateTime();
                $suggestion->setPostDate($date);
                $suggestion->setStatus("pending");
                $imgFile = $form->get('img')->getData();

                if ($imgFile) {
                    $imgFileName = $fileUploader->upload($imgFile);
                    $suggestion->setImg($imgFileName);
                }

                $suggestion->addPlayersSuggestion($this->getUser());
                $entityManager->persist($suggestion); //traditional prepare / execute in SQL MANDATORY for sql equivalents to INSERT 
                $entityManager->flush();

                $this->addFlash // need to be logged as user to see the flash messages build-in Symfony
                (
                    'notice',
                    'Your created a suggestion'
                );

                return $this->redirectToRoute('community'); //redirect to list stagiaires if everything is ok
            }
        
        return $this->render("community/new.html.twig", ['formNewSuggestion' => $form, 'edit' => $suggestion->getId()]);
    }

    #[Route('community/suggestion/detail/{title}{player}', name: 'detailSuggestion')]
    public function detail(Suggestion $suggestion, Player $player): Response
    {
        return $this->render('community/detail.html.twig', [
            'suggestion' => $suggestion,
            'player' => $player
        ]);
    }

    #[Route('community/suggestion/{id}/like/{player}', name: 'addLikeSuggestion')]
    public function addLike(Suggestion $suggestion, Player $player, EntityManagerInterface $entityManager): Response
    {
        //Banned users should not be able to like unlike
        // Get the current user
        $user = $this->getUser();

        // Check if the user has the "ROLE_BANNED"
        if ($this->isGranted('ROLE_BANNED', $user)) {
            // If the user has the "ROLE_BANNED", throw an AccessDeniedException
            throw new AccessDeniedException('You have been banned. Check your account.');
        }

        $suggestion->addPlayersLike($player);
        $entityManager->flush();
        return $this->redirectToRoute('community');            
    }

    #[Route('community/suggestion/{id}/unlike/{player}', name: 'unlikeSuggestion')]
    public function unLike(Suggestion $suggestion, Player $player, EntityManagerInterface $entityManager): Response
    {
        //Banned users should not be able to like unlike
        // Get the current user
        $user = $this->getUser();

        // Check if the user has the "ROLE_BANNED"
        if ($this->isGranted('ROLE_BANNED', $user)) {
            // If the user has the "ROLE_BANNED", throw an AccessDeniedException
            throw new AccessDeniedException('You have been banned. Check your account.');
        }

        $suggestion->removePlayersLike($player);
        $entityManager->flush();
        return $this->redirectToRoute('community');
    }

    #[Route('community/suggestion/{id}/delete/{player}', name: 'deleteSuggestion')]
    public function delete(Suggestion $suggestion, Player $player, EntityManagerInterface $entityManager): Response
    {
        //Banned users should not be able to delete their post
        // Get the current user
        $user = $this->getUser();

        // Check if the user has the "ROLE_BANNED"
        if ($this->isGranted('ROLE_BANNED', $user)) {
            // If the user has the "ROLE_BANNED", throw an AccessDeniedException
            throw new AccessDeniedException('You have been banned. Check your account.');
        }

        if ($player == $this->getUser()) //Safeguard so suggestions can only be removed by author
        {
            $suggestion->removePlayersSuggestion($player);
            $entityManager->remove($suggestion);
            $entityManager->flush();
            return $this->redirectToRoute('community');            
        }

        else
        {
            $this->addFlash(
                'error',
                "You can't do that !"
            );
            return $this->redirectToRoute("community");
        }
    }

    #[Route('community/ajax/{id}/changeStatus', name: 'changeStatus')]
    public function changeStatus(Suggestion $suggestion, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isXmlHttpRequest()) { // Check if it's an AJAX request
            $status = $request->request->get('status'); // Get the new status from the request
    
            // Update the status of the suggestion
            $suggestion->setStatus($status);
            $entityManager->flush();
    
            // Return a JSON response
            return new JsonResponse(['status' => $status]);
        }
    
        // If it's not an AJAX request, return a 400 Bad Request response
        return new Response('This is not ajax!', 400);
    }

}
