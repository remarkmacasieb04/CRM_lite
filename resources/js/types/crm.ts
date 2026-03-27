export type ClientStatusOption = {
    value: string;
    label: string;
};

export type ClientListFilters = {
    search: string | null;
    status: string | null;
    archived: string | null;
};

export type ClientListItem = {
    id: number;
    name: string;
    company: string | null;
    email: string | null;
    phone: string | null;
    status: string | null;
    status_label: string | null;
    budget: string | null;
    source: string | null;
    last_contacted_at: string | null;
    follow_up_at: string | null;
    archived_at: string | null;
    notes_count: number;
    updated_at: string | null;
};

export type ClientNote = {
    id: number;
    content: string;
    created_at: string | null;
    author: {
        id: number | null;
        name: string | null;
        email: string | null;
    };
};

export type ClientActivityEntry = {
    id: number;
    type: string | null;
    type_label: string | null;
    description: string;
    created_at: string | null;
    actor: {
        id: number | null;
        name: string | null;
        email: string | null;
    };
    properties: Record<string, unknown>;
};

export type ClientDetail = Omit<ClientListItem, 'notes_count'> & {
    created_at: string | null;
    notes: ClientNote[];
    activities: ClientActivityEntry[];
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    from: number | null;
    last_page: number;
    links: PaginationLink[];
    per_page: number;
    to: number | null;
    total: number;
};

export type DashboardStats = {
    totalClients: number;
    activeClients: number;
    leads: number;
    overdueFollowUps: number;
    followUpsDueSoon: number;
};

export type DashboardRecentClient = {
    id: number;
    name: string;
    company: string | null;
    status: string | null;
    status_label: string | null;
    follow_up_at: string | null;
    updated_at: string | null;
};

export type DashboardRecentNote = {
    id: number;
    content: string;
    created_at: string | null;
    client: {
        id: number | null;
        name: string | null;
        status: string | null;
        status_label: string | null;
    };
};

export type DashboardReminderItem = {
    id: number;
    name: string;
    company: string | null;
    status: string | null;
    status_label: string | null;
    follow_up_at: string | null;
    last_contacted_at: string | null;
    email: string | null;
    phone: string | null;
};

export type DashboardReminderGroup = {
    overdue: DashboardReminderItem[];
    upcoming: DashboardReminderItem[];
};

export type DashboardActivityItem = {
    id: number;
    type: string | null;
    type_label: string | null;
    description: string;
    created_at: string | null;
    client: {
        id: number | null;
        name: string | null;
        status: string | null;
        status_label: string | null;
    };
};
