<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Handlers\Books;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Basic\App\Events\BookChangeEvent;
use Rukavishnikov\Php\Basic\App\Events\EventDispatcher;
use Rukavishnikov\Php\Basic\App\Exceptions\InternalServerErrorHttpException;
use Rukavishnikov\Php\Basic\App\Exceptions\NotFoundHttpException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\BookRepositoryInterface;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookDeleteException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookGetException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookNotFoundException;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class DeleteAction implements RequestHandlerInterface
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
