<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180726122356 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, booking_id INT NOT NULL, address_id INT NOT NULL, total_ht INT NOT NULL, date DATETIME NOT NULL, UNIQUE INDEX UNIQ_F52993983301C60 (booking_id), UNIQUE INDEX UNIQ_F5299398F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, customer_id INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, INDEX IDX_E00CEDDE54177093 (room_id), INDEX IDX_E00CEDDE9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking_options (id INT AUTO_INCREMENT NOT NULL, room_option_id INT NOT NULL, booking_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_8A8BBFA73B6DFD06 (room_option_id), INDEX IDX_8A8BBFA73301C60 (booking_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room_type (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, street VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, address_cpl VARCHAR(255) DEFAULT NULL, INDEX IDX_D4E6F819395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, room_type_id INT NOT NULL, name VARCHAR(255) NOT NULL, capacity INT NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, hourly_price INT NOT NULL, daily_price INT NOT NULL, weekly_price INT NOT NULL, monthly_price INT NOT NULL, INDEX IDX_729F519B296E3073 (room_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room_option (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, price INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room_option_room_type (room_option_id INT NOT NULL, room_type_id INT NOT NULL, INDEX IDX_BC8A1C823B6DFD06 (room_option_id), INDEX IDX_BC8A1C82296E3073 (room_type_id), PRIMARY KEY(room_option_id, room_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993983301C60 FOREIGN KEY (booking_id) REFERENCES booking (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE54177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE booking_options ADD CONSTRAINT FK_8A8BBFA73B6DFD06 FOREIGN KEY (room_option_id) REFERENCES room_option (id)');
        $this->addSql('ALTER TABLE booking_options ADD CONSTRAINT FK_8A8BBFA73301C60 FOREIGN KEY (booking_id) REFERENCES booking (id)');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F819395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B296E3073 FOREIGN KEY (room_type_id) REFERENCES room_type (id)');
        $this->addSql('ALTER TABLE room_option_room_type ADD CONSTRAINT FK_BC8A1C823B6DFD06 FOREIGN KEY (room_option_id) REFERENCES room_option (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_option_room_type ADD CONSTRAINT FK_BC8A1C82296E3073 FOREIGN KEY (room_type_id) REFERENCES room_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE employee ADD role_id INT NOT NULL');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A1D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('CREATE INDEX IDX_5D9F75A1D60322AC ON employee (role_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993983301C60');
        $this->addSql('ALTER TABLE booking_options DROP FOREIGN KEY FK_8A8BBFA73301C60');
        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519B296E3073');
        $this->addSql('ALTER TABLE room_option_room_type DROP FOREIGN KEY FK_BC8A1C82296E3073');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398F5B7AF75');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE54177093');
        $this->addSql('ALTER TABLE employee DROP FOREIGN KEY FK_5D9F75A1D60322AC');
        $this->addSql('ALTER TABLE booking_options DROP FOREIGN KEY FK_8A8BBFA73B6DFD06');
        $this->addSql('ALTER TABLE room_option_room_type DROP FOREIGN KEY FK_BC8A1C823B6DFD06');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE booking_options');
        $this->addSql('DROP TABLE room_type');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE room_option');
        $this->addSql('DROP TABLE room_option_room_type');
        $this->addSql('DROP INDEX IDX_5D9F75A1D60322AC ON employee');
        $this->addSql('ALTER TABLE employee DROP role_id');
    }
}
