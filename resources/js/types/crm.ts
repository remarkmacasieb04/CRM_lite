export type ClientStatusOption = {
    value: string;
    label: string;
};

export type TaskStatusOption = {
    value: string;
    label: string;
};

export type TaskPriorityOption = {
    value: string;
    label: string;
};

export type ClientListFilters = {
    search: string | null;
    status: string | null;
    archived: string | null;
    tag: string | null;
    follow_up: string | null;
    stale: string | null;
};

export type ClientTag = {
    id: number;
    name: string;
    slug: string;
};

export type SavedClientView = {
    id: number;
    name: string;
    href: string;
    is_active: boolean;
    filters: Partial<ClientListFilters>;
};

export type SmartClientView = {
    key: string;
    name: string;
    description: string;
    count: number;
    href: string;
    is_active: boolean;
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
    tags: ClientTag[];
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

export type ClientCommunication = {
    id: number;
    channel: string | null;
    channel_label: string | null;
    direction: string | null;
    direction_label: string | null;
    subject: string | null;
    summary: string;
    happened_at: string | null;
    author: {
        id: number | null;
        name: string | null;
        email: string | null;
    };
    client?: {
        id: number | null;
        name: string | null;
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

export type ClientAttachment = {
    id: number;
    original_name: string;
    mime_type: string | null;
    size: number;
    created_at: string | null;
};

export type ClientDocument = {
    id: number;
    type: string | null;
    type_label: string | null;
    title: string;
    document_number: string;
    status: string | null;
    status_label: string | null;
    amount: string | null;
    currency: string;
    issued_at: string | null;
    due_at: string | null;
    resolved_at: string | null;
    notes: string | null;
    is_portal_visible: boolean;
};

export type ClientPortalShare = {
    id: number;
    token: string;
    expires_at: string | null;
    last_viewed_at: string | null;
    revoked_at: string | null;
    created_at: string | null;
    portal_url: string;
};

export type ClientDetail = Omit<ClientListItem, 'notes_count'> & {
    created_at: string | null;
    notes: ClientNote[];
    activities: ClientActivityEntry[];
    attachments: ClientAttachment[];
    tasks: TaskItem[];
    communications: ClientCommunication[];
    documents: ClientDocument[];
    portal_share: ClientPortalShare | null;
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
    openTasks: number;
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

export type DashboardTaskItem = TaskItem;

export type DashboardCommunicationItem = ClientCommunication;

export type TaskItem = {
    id: number;
    title: string;
    description: string | null;
    status: string | null;
    status_label: string | null;
    priority: string | null;
    priority_label: string | null;
    due_at: string | null;
    completed_at: string | null;
    client: {
        id: number | null;
        name: string | null;
    };
    assignee: {
        id: number | null;
        name: string | null;
        email: string | null;
    };
    creator: {
        id: number | null;
        name: string | null;
        email: string | null;
    };
};

export type DocumentTypeOption = {
    value: string;
    label: string;
};

export type DocumentStatusOption = {
    value: string;
    label: string;
};

export type CommunicationChannelOption = {
    value: string;
    label: string;
};

export type CommunicationDirectionOption = {
    value: string;
    label: string;
};
