<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', function (Request $request, Response $response, array $args) {
    $redis = $this->redis;

    $view_value = [];

    $q_all = $redis->lrange('q', 0, -1);
    $q_all = array_reverse($q_all);
    $len_q = count($q_all);
    
    foreach ($q_all as $index => $q) {
        $a = $redis->lrange('a_'.($len_q - $index), 0, -1);
        $view_value[] = [
            'q_id' => $len_q - $index,
            'q' => $q,
            'a' => $a
        ];
    }

    return $this->twig->render($response, 'index.html', ['view_value' => $view_value]);
});

$app->post('/answer', function (Request $request, Response $response, array $args) {
    $redis = $this->redis;

    $q_id = $request->getQueryParams()['q'];
    $answer = $request->getParsedBody()['answer'];
    $redis->rpush('a_'.$q_id, $answer);

    return $response->withStatus(302)->withHeader('Location', '/');
});

$app->post('/question', function (Request $request, Response $response, array $args) {
    $redis = $this->redis;

    $question = $request->getParsedBody()['question'];
    $redis->rpush('q', $question);

    return $response->withStatus(302)->withHeader('Location', '/');
});

