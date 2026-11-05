import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';

/** Read-only queries against the configured local database using the MySQL client. */
export function query<T>(sql: string): T[] {
    const config = Object.fromEntries(
        readFileSync('.env', 'utf8')
            .split(/\r?\n/)
            .flatMap((line) => {
                const match = line.match(/^([A-Z_]+)=(.*)$/);
                return match
                    ? [[match[1], match[2].replace(/^["']|["']$/g, '')]]
                    : [];
            }),
    );
    const client =
        process.env.FIRESIGHT_MYSQL_CLIENT || 'D:/xampp/mysql/bin/mysql.exe';
    const output = execFileSync(
        client,
        [
            '-h',
            config.DB_HOST,
            '-P',
            config.DB_PORT,
            '-u',
            config.DB_USERNAME,
            config.DB_DATABASE,
            '--batch',
            '--raw',
            '--skip-column-names',
            '-e',
            sql,
        ],
        {
            encoding: 'utf8',
            env: { ...process.env, MYSQL_PWD: config.DB_PASSWORD || '' },
        },
    );
    return output
        .trim()
        .split('\n')
        .filter(Boolean)
        .map((line) => JSON.parse(line) as T);
}
