import { describe, expect, it } from 'vitest';

import { cn, formatCurrency } from '@/lib/utils';

describe('utils', () => {
    it('merges tailwind classes predictably', () => {
        expect(cn('px-2', 'px-4', 'text-sm')).toBe('px-4 text-sm');
    });

    it('formats pounds for dashboard metrics', () => {
        expect(formatCurrency(1234.5)).toBe('£1,234.50');
    });
});
