<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Modules\Book\Handlers;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Basic\App\HttpExceptions\InternalServerErrorHttpException;
use Rukavishnikov\Php\Basic\App\HttpExceptions\NotFoundHttpException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Events\BookChangeEvent;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\BookRepositoryInterface;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookDeleteException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookGetException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookNotFoundException;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class BookDeleteHandler implements RequestHandlerInterface
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
            $this->bookRepository->delete($id);
        } catch (BookDeleteException $e) {
            throw new InternalServerErrorHttpException(sprintf("Book with id %d delete error!", $id), 500, $e);
        }

        $bookChangeEvent = new BookChangeEvent($oldBook, null);
        $this->eventDispatcher->dispatch($bookChangeEvent);

        $data[$id] = "Book deleted!";

        $body = $this->jsonHelper->encode($data);
        $this->response->getBody()->write($body);

        return $this->response;
    }
}
