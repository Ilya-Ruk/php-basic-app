<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Handlers\Books;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Basic\App\Exceptions\BadRequestException;
use Rukavishnikov\Php\Basic\App\Exceptions\InternalServerErrorException;
use Rukavishnikov\Php\Basic\App\Factories\Books\BookFactory;
use Rukavishnikov\Php\Basic\App\Repositories\Books\BookRepositoryInterface;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookInsertException;
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
     * @throws BadRequestException
     * @throws InternalServerErrorException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $book = BookFactory::createFromRequestData($request->getParsedBody());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestException($e->getMessage(), 400, $e);
        }

        $nextBookId = $this->bookRepository->getNextId();

        try {
            $this->bookRepository->add($book->withId($nextBookId));
        } catch (BookInsertException $e) {
            throw new InternalServerErrorException($e->getMessage(), 500, $e);
        }

        $data[$nextBookId] = "Book inserted!";

        $body = $this->jsonHelper->encode($data);
        $this->response->getBody()->write($body);

        return $this->response->withStatus(201); // Created
    }
}
