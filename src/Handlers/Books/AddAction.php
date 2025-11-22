<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Handlers\Books;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Basic\App\Exceptions\BadRequestHttpException;
use Rukavishnikov\Php\Basic\App\Exceptions\InternalServerErrorHttpException;
use Rukavishnikov\Php\Basic\App\Factories\Books\BookFactory;
use Rukavishnikov\Php\Basic\App\Repositories\Books\BookRepositoryInterface;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookAddException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookGetNextIdException;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class AddAction implements RequestHandlerInterface
{
    /**
     * @param BookRepositoryInterface $bookRepository
     * @param JsonHelper $jsonHelper
     * @param ResponseInterface $response
     */
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private JsonHelper $jsonHelper,
        private ResponseInterface $response,
    ) {
    }

    /**
     * @inheritDoc
     * @throws BadRequestHttpException
     * @throws InternalServerErrorHttpException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $book = BookFactory::createFromRequestData($request->getParsedBody());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), 400, $e);
        }

        try {
            $nextId = $this->bookRepository->getNextId();
            $bookWithId = $book->withId($nextId);

            $this->bookRepository->add($bookWithId);
        } catch (BookGetNextIdException|BookAddException $e) {
            throw new InternalServerErrorHttpException('Book add error!', 500, $e);
        }

        $data[$nextId] = "Book added!";

        $body = $this->jsonHelper->encode($data);
        $this->response->getBody()->write($body);

        return $this->response->withStatus(201); // Created
    }
}
