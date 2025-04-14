<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Student Management System Tables
 */
final class Version20250415000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create student management system tables';
    }

    public function up(Schema $schema): void
    {
        // Create Teacher table
        $this->addSql('CREATE TABLE teacher (
            id INT AUTO_INCREMENT NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            department VARCHAR(100) DEFAULT NULL,
            hire_date DATE DEFAULT NULL,
            UNIQUE INDEX UNIQ_B0F6A6D5E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create Student table
        $this->addSql('CREATE TABLE student (
            id INT AUTO_INCREMENT NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            birth_date DATE DEFAULT NULL,
            enrollment_date DATE NOT NULL,
            address VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            UNIQUE INDEX UNIQ_B723AF33E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create Course table
        $this->addSql('CREATE TABLE course (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(20) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            credits INT NOT NULL,
            start_date DATE DEFAULT NULL,
            end_date DATE DEFAULT NULL,
            UNIQUE INDEX UNIQ_169E600977153098 (code),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create Enrollment table
        $this->addSql('CREATE TABLE enrollment (
            id INT AUTO_INCREMENT NOT NULL,
            student_id INT NOT NULL,
            course_id INT NOT NULL,
            enrollment_date DATE NOT NULL,
            grade DOUBLE PRECISION DEFAULT NULL,
            status VARCHAR(20) DEFAULT NULL,
            INDEX IDX_DBDCD7E1CB944F1A (student_id),
            INDEX IDX_DBDCD7E1591CC992 (course_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create Assignment table
        $this->addSql('CREATE TABLE assignment (
            id INT AUTO_INCREMENT NOT NULL,
            course_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            due_date DATE DEFAULT NULL,
            max_score DOUBLE PRECISION DEFAULT NULL,
            INDEX IDX_30C544BA591CC992 (course_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Add foreign key constraints
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1CB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BA591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop tables in reverse order
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1CB944F1A');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E1591CC992');
        $this->addSql('ALTER TABLE assignment DROP FOREIGN KEY FK_30C544BA591CC992');
        $this->addSql('DROP TABLE teacher');
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE enrollment');
        $this->addSql('DROP TABLE assignment');
    }
}