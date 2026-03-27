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
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    connected_providers: ConnectedProvider[];
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
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
