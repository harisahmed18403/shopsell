export interface AuthUser {
    id: number;
    name: string;
    email: string;
    is_super_admin?: boolean;
    email_verified_at?: string | null;
}

export interface FlashMessages {
    success?: string | null;
    error?: string | null;
    status?: string | null;
}

export interface SharedPageProps {
    app: {
        name: string;
        url: string;
    };
    auth: {
        user: AuthUser | null;
    };
    flash: FlashMessages;
    routing: {
        current: string | null;
        previous: string | null;
    };
    errors: Record<string, string | Record<string, string>>;
}
