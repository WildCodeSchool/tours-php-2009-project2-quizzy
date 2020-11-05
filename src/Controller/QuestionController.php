<?php

namespace App\Controller;

use App\Model\QuestionManager;
use App\Model\ChoiceManager;

/**
 * Class QuestionController
 * Uses QuestionManager and ChoiceManager
 * Creates index and result views that relate to the main functionality of the site :
 * seeing and answering random questions.
 */
class QuestionController extends AbstractController
{

    /**
     * Displays a random question and its possible choices.
     * @return string
     */
    public function index(): string
    {
        $errors = [];
        $questionManager = new QuestionManager();
        $question = $questionManager->selectOneRandom();
        if (empty($question)) {
            $errors[] = "Je n'ai pas trouvé de question o(╥﹏╥)o Essaies d'en ajouter une? ^_^";
        }

        if (isset($question["id"])) {
            $choices = $questionManager->selectChoices($question["id"]);
        } else {
            $choices = [];
        }

        if (empty($choices)) {
            $errors[] = "Je n'ai pas trouvé de choix valide :(";
        }

        return $this->twig->render(
            'Question/index.html.twig',
            ['question' => $question ,
            'choices' => $choices ,
            'errors' => $errors]
        );
    }

    /**
     * Displays a message that tells if the user answered the question correctly.
     * Redirects to index if $_POST['choice'] does not exist or is empty.
     * @return string
     */
    public function result(): string
    {
        $message = "";
        $choiceManager = new ChoiceManager();

        // Check if id is an int and > 0 and displays an error message if id not found in choice table.
        try {
            $id = filter_input(INPUT_POST, 'choice', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
            if ($id) {
                $choice = $choiceManager->selectOneById($id);
                if ($choice['validity'] == 1) {
                    $message = "Gagné !";
                } else {
                    $message = "Perdu !";
                }
            } else {
                // Redirection to index
                header("Location: index");
            }
        } catch (\Exception $e) {
            $message = "Une erreur est survenue :(";
        }
        
        return $this->twig->render(
            'Question/result.html.twig',
            ['message' => $message]
        );
    }
}
