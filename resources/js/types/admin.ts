export type RoleOption = {
    value: string;
    label: string;
};

export type AdminUserListItem = {
    id: number;
    name: string;
    email: string;
    role: string | null;
    role_label: string | null;
    email_verified_at: string | null;
    receives_follow_up_reminders: boolean;
    last_follow_up_digest_sent_at: string | null;
    clients_count: number;
    created_at: string | null;
};
