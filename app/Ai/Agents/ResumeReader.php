<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

class ResumeReader implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    protected string $model = 'claude-3-5-sonnet-20241022';

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<PROMPT
            You are a resume parser.

            Extract candidate details from the given resume text.
        PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'first_name' => $schema->string(),
            'last_name' => $schema->string(),
            'email' => $schema->string(),
            'mobile' => $schema->string(),
            'address' => $schema->string(),

            'education' => $schema->array(
                $schema->object([
                    'degree' => $schema->string(),
                    'institution' => $schema->string(),
                    'year' => $schema->string(),
                ])
            ),

            'experience' => $schema->array(
                $schema->object([
                    'company' => $schema->string(),
                    'role' => $schema->string(),
                    'duration' => $schema->string(),
                ])
            ),
        ];
    }

    /**
     * Get the list of messages comprising the conversation so far.
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
}
