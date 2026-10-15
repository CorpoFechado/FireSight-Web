import {
    Bell,
    FileText,
    Flame,
    Home,
    Map,
    MapPin,
    UserRound,
} from 'lucide-react';

const quickActions = [
    { label: 'Report Incident', icon: Flame },
    { label: 'View Map', icon: Map },
    { label: 'Notifications', icon: Bell },
    { label: 'My Reports', icon: FileText },
] as const;

export function PhoneMockup({ compact = false }: { compact?: boolean }) {
    return (
        <div
            className={`relative mx-auto ${compact ? 'w-[170px]' : 'w-[250px] sm:w-[280px]'}`}
            aria-hidden="true"
        >
            <div
                className={`relative rounded-[2.5rem] border-[7px] border-[#071126] bg-white shadow-[0_35px_80px_-25px_rgba(0,0,0,0.8)] ${compact ? '-rotate-6 p-2' : 'rotate-2 p-2.5'}`}
            >
                <div className="absolute top-2 left-1/2 z-10 h-4 w-20 -translate-x-1/2 rounded-full bg-[#071126]" />
                <div className="overflow-hidden rounded-[2rem] bg-firesight-blue-soft">
                    <div
                        className={`flex items-center justify-between bg-white px-4 ${compact ? 'pt-6 pb-2' : 'pt-7 pb-3'}`}
                    >
                        <img
                            src="/images/firesight-logo.png"
                            alt=""
                            className={compact ? 'h-4 w-auto' : 'h-5 w-auto'}
                        />
                        <Bell className="size-4 text-firesight-navy" />
                    </div>
                    <div
                        className={compact ? 'space-y-2 p-3' : 'space-y-3 p-4'}
                    >
                        <div className="rounded-xl bg-gradient-to-br from-red-500 to-orange-500 p-3 text-white shadow-lg shadow-red-500/20">
                            <div className="flex items-center gap-2">
                                <span className="flex size-7 items-center justify-center rounded-full bg-white/20">
                                    <Flame className="size-4" />
                                </span>
                                <div>
                                    <p className="text-[10px] font-extrabold">
                                        Fire Alert
                                    </p>
                                    <p className="text-[8px] text-white/80">
                                        Brgy. Prenza, Lian
                                    </p>
                                </div>
                            </div>
                        </div>

                        {!compact && (
                            <>
                                <p className="text-[10px] font-extrabold text-firesight-navy">
                                    Quick Actions
                                </p>
                                <div className="grid grid-cols-2 gap-2">
                                    {quickActions.map(
                                        ({ label, icon: Icon }) => (
                                            <div
                                                key={label}
                                                className="rounded-xl bg-white p-2.5 shadow-sm"
                                            >
                                                <span className="mb-1.5 flex size-7 items-center justify-center rounded-lg bg-firesight-orange-pale text-firesight-orange">
                                                    <Icon className="size-3.5" />
                                                </span>
                                                <p className="text-[8px] font-bold text-firesight-navy">
                                                    {label}
                                                </p>
                                            </div>
                                        ),
                                    )}
                                </div>
                            </>
                        )}

                        <div
                            className={`relative overflow-hidden rounded-xl bg-[#dce8fb] ${compact ? 'h-20' : 'h-28'}`}
                        >
                            <div className="absolute inset-0 [background-image:linear-gradient(#9eb5d6_1px,transparent_1px),linear-gradient(90deg,#9eb5d6_1px,transparent_1px)] [background-size:22px_22px] opacity-50" />
                            <div className="absolute top-1/2 left-1/2 flex size-7 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-firesight-orange text-white shadow-lg">
                                <MapPin className="size-4" />
                            </div>
                        </div>
                    </div>
                    <div className="flex items-center justify-around border-t border-slate-100 bg-white px-4 py-3 text-slate-400">
                        <Home className="size-4 text-firesight-orange" />
                        <Map className="size-4" />
                        <Bell className="size-4" />
                        <UserRound className="size-4" />
                    </div>
                </div>
            </div>

            {!compact && (
                <>
                    <div className="absolute top-24 -right-20 hidden w-40 rounded-2xl border border-white/30 bg-white p-3 shadow-2xl sm:block">
                        <div className="relative h-24 overflow-hidden rounded-xl bg-[#dfe9f7]">
                            <div className="absolute inset-0 [background-image:linear-gradient(#b2c5df_1px,transparent_1px),linear-gradient(90deg,#b2c5df_1px,transparent_1px)] [background-size:18px_18px] opacity-70" />
                            <span className="firesight-marker-pulse absolute top-10 left-16 size-3 rounded-full bg-firesight-orange ring-4 ring-white" />
                        </div>
                        <p className="mt-2 text-[9px] font-bold text-firesight-navy">
                            Live incident location
                        </p>
                    </div>
                    <div className="absolute right-[-4.5rem] bottom-14 hidden w-52 items-center gap-3 rounded-2xl border border-white/10 bg-firesight-navy p-3 text-white shadow-2xl sm:flex">
                        <span className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-firesight-orange/20 text-firesight-orange">
                            <Flame className="size-5" />
                        </span>
                        <div>
                            <p className="text-[10px] font-bold">
                                Fire Detected
                            </p>
                            <p className="text-[8px] text-white/65">
                                Smoke detected in your area
                            </p>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
