<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Handlers\Books;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Basic\App\Events\BookChangeEvent;
use Rukavishnikov\Php\Basic\App\Events\EventDispatcher;
use Rukavishnikov\Php\Basic\App\Exceptions\BadRequestHttpException;
use Rukavishnikov\Php\Basic\App\Exceptions\InternalServerErrorHttpException;
use Rukavishnikov\Php\Basic\App\Exceptions\NotFoundHttpException;
use Rukavishnikov\Php\Basic\App\Factories\Books\BookFactory;
use Rukavishnikov\Php\Basic\App\Repositories\Books\BookRepositoryInterface;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookEditException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookGetException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookNotFoundException;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class EditAction implements RequestHandlerInterface
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
