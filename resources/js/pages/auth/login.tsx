import { Form, Head } from '@inertiajs/react';
import { ArrowRight, LockKeyhole, Mail } from 'lucide-react';
import InputError from '@/components/input-error';
import PasskeyVerify from '@/components/passkey-verify';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    return (
        <>
            <Head title="Admin Portal" />

            {status && (
                <div className="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700">
                    {status}
                </div>
            )}

            <PasskeyVerify separator="Or sign in with email" />

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-5"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="email" className="sr-only">
                                Email address
                            </Label>
                            <div className="relative">
                                <Mail
                                    className="pointer-events-none absolute top-1/2 left-4 z-10 size-5 -translate-y-1/2 text-firesight-copy"
                                    aria-hidden="true"
                                />
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder="Email address"
                                    className="h-13 rounded-xl border-slate-200 bg-white pl-12 text-firesight-navy shadow-sm placeholder:text-slate-400"
                                />
                            </div>
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password" className="sr-only">
                                Password
                            </Label>
                            <div className="relative">
                                <LockKeyhole
                                    className="pointer-events-none absolute top-1/2 left-4 z-10 size-5 -translate-y-1/2 text-firesight-copy"
                                    aria-hidden="true"
                                />
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    placeholder="Password"
                                    className="h-13 rounded-xl border-slate-200 bg-white pr-12 pl-12 text-firesight-navy shadow-sm placeholder:text-slate-400"
                                />
                            </div>
                            <InputError message={errors.password} />
                        </div>

                        <div className="flex items-center justify-between gap-4">
                            <div className="flex items-center gap-2.5">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    tabIndex={3}
                                    className="cursor-pointer border-slate-300 data-[state=checked]:border-firesight-navy-highlight data-[state=checked]:bg-firesight-navy-highlight"
                                />
                                <Label
                                    htmlFor="remember"
                                    className="cursor-pointer text-sm font-medium text-firesight-navy"
                                >
                                    Remember me
                                </Label>
                            </div>

                            {canResetPassword && (
                                <TextLink
                                    href={request()}
                                    className="text-sm font-semibold text-firesight-navy-highlight decoration-transparent hover:text-firesight-orange"
                                    tabIndex={5}
                                >
                                    Forgot password?
                                </TextLink>
                            )}
                        </div>

                        <Button
                            type="submit"
                            size="lg"
                            className="mt-2 w-full bg-gradient-to-r from-firesight-orange to-firesight-orange-light text-base font-bold shadow-[0_14px_30px_-12px_rgba(255,106,19,0.8)]"
                            tabIndex={4}
                            disabled={processing}
                            data-test="login-button"
                        >
                            {processing ? <Spinner /> : 'Sign In'}
                            {!processing && (
                                <ArrowRight
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            )}
                        </Button>
                    </>
                )}
            </Form>
        </>
    );
}

Login.layout = {
    title: 'Admin Portal',
    description: 'Sign in to your account using your credentials',
};
