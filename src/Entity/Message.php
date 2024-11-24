<?php
namespace App\Entity;

use App\DataFixtures\MessageStatusEnum;
use App\Repository\MessageRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    /**
     * The unique identifier of the message.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The universally unique identifier (UUID) of the message.
     *
     * @var string
     */
    #[ORM\Column(type: Types::GUID)]
    private readonly string $uuid;

    /**
     * The text content of the message.
     *
     * @var string
     */
    #[ORM\Column(length: 255)]
    private string $text;

    /**
     * The status of the message (optional).
     *
     * @var string|null
     */
    #[ORM\Column(length: 255, nullable: true, enumType: MessageStatusEnum::class)]
    private ?string $status = null;

    /**
     * The creation date and time of the message.
     *
     * @var DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private readonly DateTime $createdAt;

    /**
     * Constructor to initialize required fields.
     *
     */
    public function __construct(string $text)
    {
        if (!$this->isValidText($text)) {
            throw new InvalidArgumentException('Text must not be empty or exceed 255 characters.');
        }

        $this->text = $text;
        $this->createdAt = new DateTime();
        $this->uuid = Uuid::v4()->toRfc4122();
    }

    /**
     * Get the unique identifier of the message.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the universally unique identifier (UUID) of the message.
     *
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * Get the text content of the message.
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Get the status of the message as an Enum.
     *
     * Converts the database string value to a MessageStatusEnum instance.
     * Returns null if the status is not set.
     *
     * @return MessageStatusEnum|null Returns the enum representation of the status, or null if status is not set.
     */
    public function getStatus(): ?MessageStatusEnum
    {
        if ($this->status === null) {
            // Status is not set; returning null explicitly
            return null;
        }

        return MessageStatusEnum::from($this->status);
    }

    /**
     * Get the creation date and time of the message.
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set the status of the message.
     *
     * @param MessageStatusEnum|null $status The new status of the message.
     * @return $this
     */
    public function setStatus(?MessageStatusEnum $status): static
    {
        $this->status = $status?->value;
        return $this;
    }

    /**
     * Set the text content of the message.
     *
     * @param string $text The new text content (max 255 characters, cannot be empty or whitespace only).
     * @throws InvalidArgumentException If the text exceeds 255 characters or is empty.
     * @return $this
     */
    public function setText(string $text): static
    {
        if (!$this->isValidText($text)) {
            throw new InvalidArgumentException('Text must not be empty or exceed 255 characters.');
        }

        $this->text = $text;

        return $this;
    }

    /**
     * Validate the text content.
     *
     * @param string $text The text to validate.
     * @return bool True if valid, false otherwise.
     */
    private function isValidText(string $text): bool
    {
        return strlen($text) <= 255 && trim($text) !== '';
    }
}
