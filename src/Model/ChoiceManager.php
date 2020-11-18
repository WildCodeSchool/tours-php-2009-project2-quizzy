<?php

namespace App\Model;

/**
 * Class ChoiceManager
 * Is used to communicate with the choice table in the database
 */
class ChoiceManager extends AbstractManager
{
    public const TABLE = 'choice';
    public const DATABASE_ERROR = -1;
    public const CHOICE_MAX_LENGTH = 100;
    public const CHOICE_MIN_LENGTH = 1;
    public const MIN_NUMBER_OF_VALID_CHOICES = 1;

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * Links choice and question id then inserts choice in DB and returns choice id
     * @param array $userChoice
     * @param int $questionId
     * @return int
     */
    public function addChoice(array $userChoice, int $questionId): int
    {
        try {
            // prepared request
            $statement = $this->pdo->prepare(
                "INSERT INTO " . self::TABLE .
                " (`answer` , `validity` , `question_id`) 
                VALUES (:answer , :validity , :question_id)"
            );
            $statement->bindValue('answer', $userChoice['answer'], \PDO::PARAM_STR);
            $statement->bindValue('validity', $userChoice['validity'], \PDO::PARAM_BOOL);
            $statement->bindValue('question_id', $questionId, \PDO::PARAM_INT);
        } catch (\Exception $e) {
            return self::DATABASE_ERROR;
        }

        if (
            $statement->bindValue('answer', $userChoice['answer'], \PDO::PARAM_STR) === false
            || $statement->bindValue('validity', $userChoice['validity'], \PDO::PARAM_BOOL) === false
            || $statement->bindValue('question_id', $questionId, \PDO::PARAM_INT) === false
        ) {
            return self::DATABASE_ERROR;
        }

        if ($statement->execute()) {
            return (int)$this->pdo->lastInsertId();
        }

        return self::DATABASE_ERROR;
    }

    /**
     * Choices and validity verification
     * @param array $userChoices
     * @return array
     */
    public function choicesVerifications(array $userChoices): array
    {
        $errors = [];
        $numberOfValidChoices = 0;

        // Check if each choice isn't too long or too short
        foreach ($userChoices as $userChoice) {
            if (strlen($userChoice['answer']) > self::CHOICE_MAX_LENGTH) {
                if (empty($errors[0])) {
                    $errors[0] = "L'un de tes choix est trop long! Ils doivent faire maximum "
                    . self::CHOICE_MAX_LENGTH . " caractères.";
                }
            }

            if (strlen($userChoice['answer']) < self::CHOICE_MIN_LENGTH) {
                if (empty($errors[1])) {
                    $errors[1] = "L'un de tes choix est trop court! Ils doivent faire minimum "
                    . self::CHOICE_MIN_LENGTH . " caractère.";
                }
            }

            // Count the valid choices
            if ($userChoice['validity'] === '1') {
                $numberOfValidChoices++;
            }
        }

        // Check if there is at least one good choice
        if ($numberOfValidChoices < self::MIN_NUMBER_OF_VALID_CHOICES) {
            $errors[2] = "Il te faut au minimum " . self::MIN_NUMBER_OF_VALID_CHOICES . " bonne réponse!";
        }

        return $errors;
    }
}
