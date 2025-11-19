<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Handlers\Books;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Basic\App\Exceptions\InternalServerErrorException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\BookRepositoryInterface;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookGetAllException;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class ListAction implements RequestHandlerInterface
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
     * @throws InternalServerErrorException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $bookList = $this->bookRepository->getAll();
        } catch (BookGetAllException $e) {
            throw new InternalServerErrorException('Get all books error!', 500, $e);
        }

        $data = [];

        foreach ($bookList as $book) {
            $bookId = $book->getId()->getValue();
            $data[$bookId] = $book->getAsArray();
        }

        $body = $this->jsonHelper->encode($data);
        $this->response->getBody()->write($body);

        return $this->response;
    }
}
