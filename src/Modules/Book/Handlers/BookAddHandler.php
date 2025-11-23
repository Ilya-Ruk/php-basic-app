<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Modules\Book\Handlers;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Basic\App\Events\EventDispatcher;
use Rukavishnikov\Php\Basic\App\HttpExceptions\BadRequestHttpException;
use Rukavishnikov\Php\Basic\App\HttpExceptions\InternalServerErrorHttpException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Events\BookChangeEvent;
use Rukavishnikov\Php\Basic\App\Modules\Book\Factories\BookFactory;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\BookRepositoryInterface;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookAddException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookGetNextIdException;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class BookAddHandler implements RequestHandlerInterface
{
    /**
     * @param BookRepositoryInterface $bookRepository
     * @param JsonHelper $jsonHelper
     * @param ResponseInterface $response
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private JsonHelper $jsonHelper,
        private ResponseInterface $response,
        private EventDispatcher $eventDispatcher,
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
            $newBook = BookFactory::createFromRequestData($request->getParsedBody());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), 400, $e);
        }

        try {
            $nextId = $this->bookRepository->getNextId();
            $newBookWithId = $newBook->withId($nextId);

            $this->bookRepository->add($newBookWithId);
        } catch (BookGetNextIdException|BookAddException $e) {
            throw new InternalServerErrorHttpException('Book add error!', 500, $e);
        }

        $bookChangeEvent = new BookChangeEvent(null, $newBookWithId);
        $this->eventDispatcher->dispatch($bookChangeEvent);

        $data[$nextId] = "Book added!";

        $body = $this->jsonHelper->encode($data);
        $this->response->getBody()->write($body);

        return $this->response->withStatus(201); // Created
    }
}
