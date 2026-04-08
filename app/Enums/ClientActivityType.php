<?php

namespace App\Enums;

enum ClientActivityType: string
{
    case Created = 'created';
    case Updated = 'updated';
    case NoteAdded = 'note_added';
    case AttachmentAdded = 'attachment_added';
    case AttachmentDeleted = 'attachment_deleted';
    case Imported = 'imported';
    case Contacted = 'contacted';
    case Archived = 'archived';
    case Restored = 'restored';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Client created',
            self::Updated => 'Client updated',
            self::NoteAdded => 'Note added',
            self::AttachmentAdded => 'Attachment added',
            self::AttachmentDeleted => 'Attachment removed',
            self::Imported => 'Imported from CSV',
            self::Contacted => 'Marked as contacted',
            self::Archived => 'Client archived',
            self::Restored => 'Client restored',
            self::Deleted => 'Client deleted',
        };
    }
}
