<?php
declare(strict_types=1);

namespace PhpHosts;

class HostEntry
{
    private string $type;
    private ?string $ip;
    private ?array $names;
    private ?string $comment;

    public static function forIp(string $type, string $ip, array $names): HostEntry
    {
        return new HostEntry($type, $ip, $names, null);
    }

    public static function forComment(string $comment): HostEntry
    {
        return new HostEntry(Type::COMMENT, null, null, $comment);
    }

    public function __construct(string $type, ?string $ip, ?array $names, ?string $comment)
    {
        $this->type = $type;
        $this->ip = $ip;
        $this->names = $names;
        $this->comment = $comment;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getNames(): ?array
    {
        return $this->names;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function isReal(): bool
    {
        return in_array($this->type, [Type::IPV4, Type::IPV6]);
    }

    public function concat(HostEntry $hostEntry): void
    {
        $this->names = array_merge($this->names, $hostEntry->getNames());
    }

    public function formatForFile(): string
    {
        if ($this->isReal()) {
            $names = implode(' ', $this->names);
            return "{$this->ip}\t{$names}\n";
        }

        return "# {$this->comment}\n";
    }
}
