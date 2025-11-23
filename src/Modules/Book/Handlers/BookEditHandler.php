<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Modules\Book\Handlers;

use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Basic\App\HttpExceptions\BadRequestHttpException;
use Rukavishnikov\Php\Basic\App\HttpExceptions\InternalServerErrorHttpException;
use Rukavishnikov\Php\Basic\App\HttpExceptions\NotFoundHttpException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Events\BookChangeEvent;
use Rukavishnikov\Php\Basic\App\Modules\Book\Factories\BookFactory;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\BookRepositoryInterface;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookEditException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookGetException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookNotFoundException;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class BookEditHandler implements RequestHandlerInterface
{
    /**
     * @param BookRepositoryInterface $bookRepository
     * @param JsonHelper $jsonHelper
     * @param ResponseInterface $response
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private JsonHelper $jsonHelper,
        private ResponseInterface $response,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @inheritDoc
     * @throws BadRequestHttpException
     * @throws InternalServerErrorHttpException
     * @throws NotFoundHttpException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int)$request->getAttribute('id', 0);

        try {
            $oldBook = $this->bookRepository->getById($id);
        } catch (BookGetException $e) {
            throw new InternalServerErrorHttpException(sprintf("Book with id %d get error!", $id), 500, $e);
        } catch (BookNotFoundException $e) {
            throw new NotFoundHttpException(sprintf("Book with id %d not found!", $id), 404, $e);
        }

        try {
            $newBook = BookFactory::createFromRequestData($request->getParsedBody());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), 400, $e);
        }

        try {
            $this->bookRepository->edit($id, $newBook);
        } catch (BookEditException $e) {
            throw new InternalServerErrorHttpException(sprintf("Book with id %d edit error!", $id), 500, $e);
        }

        $bookChangeEvent = new BookChangeEvent($oldBook, $newBook);
        $this->eventDispatcher->dispatch($bookChangeEvent);

        $data[$id] = "Book edited!";

        $body = $this->jsonHelper->encode($data);
        $this->response->getBody()->write($body);

        return $this->response;
    }
}
