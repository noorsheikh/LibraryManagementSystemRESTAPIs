<?php

namespace Library\Persistence;

use Doctrine\DBAL\Connection;

use Library\Domain\Book;
use Library\Domain\Copy;
use Library\Domain\Borrower;

class LibraryRepository implements Repository
{

    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public function addBook(Book $book)
    {
        try {
            $this->connection()->insert(
                'book',
                array(
                    'title' => $book->getTitle(),
                    'author' => $book->getAuthor(),
                    'isbn_number' => $book->getIsbnNumber()
                )
            );

            return $this->connection()->lastInsertId();

        } catch (\Exception $exception) {
            throw new $exception;
        }
    }

    public function getBooks()
    {
        try {
            $stmt = $this->connection()->prepare("SELECT * FROM book ");
            $stmt->execute();
            $books = $stmt->fetchAll();

            return $books;

        } catch (\Exception $exception) {
            throw new $exception;
        }
    }

    public function getBook($id)
    {
        try {
            if (empty($id)) {
                throw new Exception("Book ID cannot be null", 1);
            }

            $stmt = $this->connection()->prepare("SELECT * FROM book WHERE id = ?");
            $stmt->bindValue(1, $id);
            $stmt->execute();

            $book = $stmt->fetch();

            return $book;

        } catch (\Exception $exception) {
            throw new $exception;
        }
    }

    public function listAvailableBooks()
    {
        try {
            $stmt = $this->connection()->prepare("
                SELECT
                b.title,
                b.author,
                b.isbn_number,
                (SELECT COUNT(c.id) FROM copy c WHERE c.fk_book_id = b.id) available_copies
                FROM book b
                JOIN copy c ON c.fk_book_id = b.id
                WHERE c.fk_book_id = b.id AND available_copies > 0;
            ");
            $stmt->execute();
            $books = $stmt->fetchAll();

            return $books;

        } catch (\Exception $exception) {
            throw new $exception;
        }
    }

    public function addCopy(Copy $copy)
    {
        try {
            $this->connection()->insert(
                'copy',
                array(
                    'isbn_number' => $copy->getIsbnNumber(),
                    'copy_number' => $copy->getCopyNumber(),
                    'fk_book_id' => $copy->getBookId()
                )
            );

        } catch (\Exception $exception) {
            throw new $exception;
        }
    }

    public function addBorrower(Borrower $borrower)
    {
        try {
            $this->connection()->insert(
                'borrower',
                array(
                    'borrower_name' => $borrower->getBorrowerName(),
                    'borrower_membership_id' => $borrower->getBorrowerMembershipId(),
                    'status' => $borrower->getStatus()
                )
            );

        } catch (\Exception $exception) {
            throw new $exception;
        }
    }

    public function updateBorrower(Borrower $borrower, $id)
    {
        try {
            $this->connection()->update(
                'borrower',
                array(
                    'borrower_name' => $borrower->getBorrowerName(),
                    'borrower_membership_id' => $borrower->getBorrowerMembershipId(),
                    'status' => $borrower->getStatus()
                ),
                array(
                    'id' => $id
                )
            );
        } catch (\Exception $exception) {
            throw new $exception;
        }
    }

    private function connection()
    {
        return $this->connection;
    }

}