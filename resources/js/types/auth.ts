export type ConnectedProvider = {
    provider: string;
    label: string;
    avatar: string | null;
    provider_email: string | null;
    linked_at: string | null;
};

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string | null;
    role: string | null;
    role_label: string | null;
    can_access_admin: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    receives_follow_up_reminders: boolean;
    last_follow_up_digest_sent_at: string | null;
    connected_providers: ConnectedProvider[];
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
};

export type CurrentWorkspace = {
    id: number;
    name: string;
    slug: string;
    is_personal: boolean;
    role: string | null;
    role_label: string | null;
};

export type WorkspaceOption = {
    id: number;
    name: string;
    slug: string;
    is_personal: boolean;
    role: string | null;
};

export type FlashMessages = {
    success?: string | null;
    error?: string | null;
};

export type SocialProviderOption = {
    name: string;
    label: string;
    icon: string;
    href: string;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
