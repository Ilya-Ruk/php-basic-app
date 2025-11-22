<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Handlers\Books;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Basic\App\Exceptions\InternalServerErrorHttpException;
use Rukavishnikov\Php\Basic\App\Exceptions\NotFoundHttpException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\BookRepositoryInterface;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookGetException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookNotFoundException;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class ViewAction implements RequestHandlerInterface
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
     * @throws InternalServerErrorHttpException
     * @throws NotFoundHttpException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int)$request->getAttribute('id', 0);

        try {
            $book = $this->bookRepository->getById($id);
        } catch (BookGetException $e) {
            throw new InternalServerErrorHttpException(sprintf("Book with id %d get error!", $id), 500, $e);
        } catch (BookNotFoundException $e) {
            throw new NotFoundHttpException(sprintf("Book with id %d not found!", $id), 404, $e);
        }

        $bookId = $book->getId()->getValue();
        $data[$bookId] = $book->getAsArray();

        $body = $this->jsonHelper->encode($data);
        $this->response->getBody()->write($body);

        return $this->response;
    }
}
