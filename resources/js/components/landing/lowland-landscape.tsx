import type { SVGProps } from 'react';

export function LowlandLandscape(props: SVGProps<SVGSVGElement>) {
    return (
        <svg
            viewBox="0 0 1440 260"
            preserveAspectRatio="none"
            aria-hidden="true"
            {...props}
        >
            <path
                d="M0 186C180 164 320 174 480 188s320 9 480-5 302-10 480 7v70H0z"
                fill="#081735"
                opacity=".72"
            />
            <path
                d="M0 210c210-30 382-17 572 2 207 20 402-26 568-16 118 7 213 23 300 14v50H0z"
                fill="#06132e"
                opacity=".88"
            />
            <g fill="#07142f" opacity=".9">
                <path d="M92 184h72v43H92z" />
                <path d="m80 184 48-32 48 32z" />
                <path d="M122 199h15v28h-15z" fill="#0f2552" />
                <path d="M224 195h88v36h-88z" />
                <path d="m212 195 56-30 56 30z" />
                <path d="M278 207h18v24h-18z" fill="#0f2552" />
                <path d="M1110 188h78v42h-78z" />
                <path d="m1097 188 52-34 52 34z" />
                <path d="M1123 202h16v28h-16z" fill="#0f2552" />
                <path d="M1250 198h92v34h-92z" />
                <path d="m1238 198 58-29 58 29z" />
            </g>
            <g fill="#06132e">
                <path d="M36 224c0-17 8-27 18-27s18 10 18 27z" />
                <path d="M52 194h5v38h-5z" />
                <path d="M354 225c0-20 10-32 22-32s22 12 22 32z" />
                <path d="M373 188h6v43h-6z" />
                <path d="M1025 224c0-18 9-29 20-29s20 11 20 29z" />
                <path d="M1042 190h6v40h-6z" />
                <path d="M1370 225c0-21 10-34 23-34s23 13 23 34z" />
                <path d="M1390 186h6v46h-6z" />
            </g>
            <g fill="none" stroke="#13306b" strokeWidth="2" opacity=".72">
                <path d="M0 222c260-25 480-5 720 10s460 5 720-16" />
                <path d="M0 238c270-19 505-2 720 9s462 2 720-12" />
                <path d="M470 260c36-34 78-57 124-68" />
                <path d="M620 260c16-34 35-58 58-74" />
                <path d="M818 260c-16-34-35-58-58-74" />
                <path d="M968 260c-36-34-78-57-124-68" />
            </g>
            <path
                d="M0 251c210-9 428-5 720 2 291 7 514 5 720-4v11H0z"
                fill="#041027"
            />
        </svg>
    );
}
