<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616192214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE chat_question (id SERIAL NOT NULL, question TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_ip VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN chat_question.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE chat_response (id SERIAL NOT NULL, question_id INT NOT NULL, answer TEXT NOT NULL, relevant_videos JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_A59DCCB1E27F6BF ON chat_response (question_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN chat_response.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat_response ADD CONSTRAINT FK_A59DCCB1E27F6BF FOREIGN KEY (question_id) REFERENCES chat_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE chat_response DROP CONSTRAINT FK_A59DCCB1E27F6BF
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE chat_question
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE chat_response
        SQL);
    }
}
