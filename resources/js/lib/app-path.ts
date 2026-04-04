export function appPath(basePath: string, path: string): string {
    const normalizedBasePath = basePath.replace(/\/+$/, '');
    const normalizedPath = path.startsWith('/') ? path : `/${path}`;

    return normalizedBasePath ? `${normalizedBasePath}${normalizedPath}` : normalizedPath;
}
