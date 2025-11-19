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
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookEditException;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class EditAction implements RequestHandlerInterface
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
        $id = (int)$request->getAttribute('id', 0);

        try {
            $book = BookFactory::createFromRequestData($request->getParsedBody());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestException($e->getMessage(), 400, $e);
        }

        try {
            $this->bookRepository->edit($id, $book);
        } catch (BookEditException $e) {
            throw new InternalServerErrorException(sprintf("Book with id %d edit error!", $id), 500, $e);
        }

        $data[$id] = "Book edited!";

        $body = $this->jsonHelper->encode($data);
        $this->response->getBody()->write($body);

        return $this->response;
    }
}
