<?php

/**
 * Plugin Name: answers Mutations
 * Description: Adds WPGraphQL Mutations
 * Author: Alexandra Spalato
 * Author URI: http://alexandraspalato.com/
 * Version: 1.0
 * Text Domain: answers-mutations
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}



add_action('graphql_register_types', function () {
    register_graphql_mutation('resultMutation', [
        'inputFields' => [
            'emailInput' => [
                'type' => 'String',
                'description' =>'email field'
            ],
            'firstNameInput' => [
                'type' => 'String',
                'description' => 'First Name Field'
            ],
            'answersInput' => [
                'type' => 'String',
                'description' => 'Answers to the quizz'
            ],

            'resultsInput' => [
                'type' => ['list_of' => 'ID'],
                'description' => 'Detected Dragons'
            ]

        ],
        'outputFields' => [
            'resultSubmitted' => [
                'type' => 'Boolean',
                'description' =>'Result submission successfull or not',
            ]
        ],
        'mutateAndGetPayload' => function ($input, $context, $info) {
            // wp_send_json($input); //for debugging in graphiql

            //validate the input: make sure email is valid, do we need 3 items absolutely
            if (!is_email($input['emailInput'])) {
                throw new \GraphQL\Error\UserError('The email is invalid');
            }
            $existing_vote = get_page_by_title($input['emailInput'], 'OBJECT', 'answers');
            if ($existing_vote) {
                throw new \GraphQL\Error\UserError('You have already submitted a dragons questionnaire from this email');
            }
            $post_id = wp_insert_post([
                'post_type' => 'answers',
                'post_title' => sanitize_text_field($input['emailInput']),
                'post_content' => sanitize_text_field($input['firstNameInput']),
                'post_status' => 'publish'

            ]);
            update_field('results_dragons', $input['resultsInput'], $post_id);
            return  [
                "voteSubmitted" => true
            ];
        }
    ]);
});