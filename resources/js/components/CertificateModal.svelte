<script lang="ts">
    import X from '@lucide/svelte/icons/x';
    import Printer from '@lucide/svelte/icons/printer';
    import Award from '@lucide/svelte/icons/award';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';

    interface CourseData {
        id: number;
        title: string;
        level?: string;
        estimated_duration_hours?: number;
        tutor?: {
            name: string;
            title?: string;
        } | null;
        class_group?: {
            name: string;
        } | null;
        company?: {
            name: string;
        } | null;
        completed_at?: string | null;
        certificate_code?: string;
    }

    interface UserData {
        name: string;
        email?: string;
        job_title?: string;
        department?: string;
        company?: {
            name: string;
        } | null;
    }

    interface Props {
        isOpen: boolean;
        course: CourseData | null;
        user: UserData | null;
        completedAt?: string | null;
        certificateCode?: string | null;
        onClose: () => void;
    }

    let {
        isOpen = false,
        course = null,
        user = null,
        completedAt = null,
        certificateCode = null,
        onClose,
    }: Props = $props();

    function handlePrint() {
        window.print();
    }

    let issueDate = $derived(
        completedAt ||
        course?.completed_at ||
        new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
    );

    let certId = $derived(
        certificateCode ||
        course?.certificate_code ||
        `EDJ-CERT-${course?.id || 101}-${Math.floor(1000 + Math.random() * 9000)}-${new Date().getFullYear()}`
    );
</script>

{#if isOpen && course && user}
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-6 overflow-hidden bg-black/75 backdrop-blur-xs certificate-backdrop">
        <!-- Backdrop dismiss click -->
        <button
            type="button"
            class="fixed inset-0 bg-transparent print:hidden cursor-default"
            aria-label="Tutup"
            onclick={onClose}
        ></button>

        <!-- Bottom Sheet / Dialog Container -->
        <div class="relative z-10 w-full max-w-4xl bg-card border border-border rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[95vh] certificate-modal-box animate-in slide-in-from-bottom duration-300 sm:slide-in-from-bottom-0 sm:zoom-in-95 pb-[max(0.5rem,env(safe-area-inset-bottom))] sm:pb-0">
            <!-- Mobile Bottom Sheet Drag Handle -->
            <div class="flex sm:hidden justify-center pt-2.5 pb-1 bg-muted/40 print:hidden shrink-0">
                <div class="h-1.5 w-12 rounded-full bg-muted-foreground/30"></div>
            </div>

            <!-- Header Toolbar (Hidden when printing) -->
            <div class="flex items-center justify-between px-4 sm:px-5 py-3 sm:py-3.5 bg-muted/40 border-b border-border/80 print:hidden shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="size-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold shrink-0">
                        <Award class="size-4.5" />
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-foreground truncate">Sertifikat Kelulusan</h3>
                        <p class="text-[10px] sm:text-[11px] text-muted-foreground truncate font-mono">ID: {certId}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button
                        type="button"
                        onclick={handlePrint}
                        class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs transition-colors"
                    >
                        <Printer class="size-3.5" />
                        <span class="hidden sm:inline">Cetak / Simpan PDF</span>
                        <span class="sm:hidden">Cetak</span>
                    </button>
                    <button
                        type="button"
                        onclick={onClose}
                        class="p-2 rounded-xl text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                        aria-label="Tutup"
                    >
                        <X class="size-4.5" />
                    </button>
                </div>
            </div>

            <!-- Scrollable Certificate Canvas Container -->
            <div class="p-3 sm:p-8 overflow-y-auto overflow-x-auto bg-slate-900/30 flex justify-center items-start sm:items-center">
                <!-- PRINT TARGET CONTAINER -->
                <div
                    id="certificate-print-area"
                    class="relative w-full max-w-[820px] aspect-[1.414/1] bg-[#fafafa] text-slate-800 p-6 sm:p-10 shadow-2xl rounded-lg border-8 border-double border-[#d4af37] select-none flex flex-col justify-between overflow-hidden"
                    style="background: linear-gradient(135deg, #ffffff 0%, #fdfbf7 50%, #fbf8f0 100%);"
                >
                    <!-- Background Watermark Logo -->
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.035] overflow-hidden">
                        <img src="/images/logo.png" alt="Eduja Watermark" class="w-[500px] grayscale" />
                    </div>

                    <!-- Inner Decorative Corner Borders -->
                    <div class="absolute top-2 left-2 size-8 border-t-2 border-l-2 border-[#b8860b]"></div>
                    <div class="absolute top-2 right-2 size-8 border-t-2 border-r-2 border-[#b8860b]"></div>
                    <div class="absolute bottom-2 left-2 size-8 border-b-2 border-l-2 border-[#b8860b]"></div>
                    <div class="absolute bottom-2 right-2 size-8 border-b-2 border-r-2 border-[#b8860b]"></div>

                    <!-- 1. Header: Brand Logo & Title -->
                    <div class="text-center relative z-10 space-y-2">
                        <div class="flex items-center justify-center gap-3">
                            <img src="/images/logo.png" alt="Eduja Learning Media" class="h-9 sm:h-11 object-contain" />
                        </div>
                        <div class="text-[9px] sm:text-[11px] font-bold tracking-[0.25em] text-[#855e0c] uppercase">
                            Eduja Corporate Learning & Development Academy
                        </div>
                        <div class="pt-1">
                            <h1 class="text-xl sm:text-3xl font-extrabold tracking-wide uppercase text-[#0d2a54] font-serif">
                                Sertifikat Kelulusan
                            </h1>
                            <p class="text-[10px] sm:text-xs font-medium text-slate-500 italic tracking-wider">
                                Certificate of Completion & Professional Competency
                            </p>
                        </div>
                        <!-- Decorative separator -->
                        <div class="flex items-center justify-center gap-2 pt-1">
                            <div class="h-px w-16 sm:w-28 bg-gradient-to-r from-transparent to-[#d4af37]"></div>
                            <div class="size-2 rotate-45 bg-[#d4af37]"></div>
                            <div class="h-px w-16 sm:w-28 bg-gradient-to-l from-transparent to-[#d4af37]"></div>
                        </div>
                    </div>

                    <!-- 2. Body: Recipient & Course Details -->
                    <div class="text-center relative z-10 py-3 sm:py-5 space-y-2.5">
                        <p class="text-[11px] sm:text-xs text-slate-600 font-serif italic">
                            Diberikan dengan bangga kepada:
                        </p>

                        <div class="space-y-0.5">
                            <h2 class="text-xl sm:text-3xl font-bold text-[#0d2a54] font-serif underline decoration-[#d4af37]/60 underline-offset-4">
                                {user.name}
                            </h2>
                            <p class="text-[11px] sm:text-xs font-medium text-slate-500">
                                {user.job_title || user.department || 'Peserta Program Pelatihan'}
                                {#if user.company?.name || course.company?.name}
                                    <span class="mx-1">•</span>
                                    <span>{user.company?.name || course.company?.name}</span>
                                {/if}
                            </p>
                        </div>

                        <p class="text-[10px] sm:text-xs text-slate-600 max-w-xl mx-auto leading-relaxed pt-1">
                            Atas keberhasilannya dalam menyelesaikan seluruh rangkaian modul pelatihan, materi pembelajaran, serta evaluasi kompetensi pada program kelas:
                        </p>

                        <div class="py-1">
                            <h3 class="text-base sm:text-xl font-extrabold text-[#003882] tracking-tight">
                                "{course.title}"
                            </h3>
                            <div class="flex items-center justify-center gap-3 text-[10px] sm:text-[11px] text-slate-500 mt-1">
                                {#if course.estimated_duration_hours}
                                    <span class="font-medium">⏱️ {course.estimated_duration_hours} Jam Pembelajaran</span>
                                    <span>•</span>
                                {/if}
                                {#if course.class_group?.name}
                                    <span class="font-medium">📂 {course.class_group.name}</span>
                                    <span>•</span>
                                {/if}
                                <span class="font-semibold text-emerald-700">Terakreditasi Penuh</span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Footer: Signatures, Seal & Verification -->
                    <div class="relative z-10 pt-2 border-t border-[#d4af37]/40 flex items-end justify-between text-slate-700">
                        <!-- Left: Instructor Signature -->
                        <div class="text-center w-36 sm:w-44">
                            <div class="h-10 sm:h-12 flex items-end justify-center pb-1">
                                <span class="font-serif italic text-lg sm:text-xl text-[#0d2a54] font-bold opacity-80 font-signature">
                                    {course.tutor?.name || 'Tutor Instruktur'}
                                </span>
                            </div>
                            <div class="border-t border-slate-400 pt-1">
                                <p class="text-[10px] sm:text-xs font-bold text-[#0d2a54] leading-tight">
                                    {course.tutor?.name || 'Instruktur Profesional'}
                                </p>
                                <p class="text-[8px] sm:text-[10px] text-slate-500">
                                    {course.tutor?.title || 'Lead Corporate Tutor'}
                                </p>
                            </div>
                        </div>

                        <!-- Center: Gold Embossed Seal -->
                        <div class="flex flex-col items-center justify-center shrink-0">
                            <div class="size-16 sm:size-20 rounded-full border-2 border-dashed border-[#b8860b] bg-gradient-to-br from-[#ffd700]/20 via-[#d4af37]/30 to-[#b8860b]/20 flex flex-col items-center justify-center p-1 text-center shadow-inner">
                                <ShieldCheck class="size-5 sm:size-6 text-[#855e0c]" />
                                <span class="text-[7px] sm:text-[8px] font-extrabold uppercase tracking-tight text-[#593e06] mt-0.5">
                                    TERAKREDITASI
                                </span>
                                <span class="text-[6px] text-slate-600 font-mono">
                                    EDUJA VERIFIED
                                </span>
                            </div>
                            <div class="text-[8px] sm:text-[9px] text-slate-500 font-mono mt-1">
                                {certId}
                            </div>
                        </div>

                        <!-- Right: Academic Director Signature -->
                        <div class="text-center w-36 sm:w-44">
                            <div class="h-10 sm:h-12 flex items-end justify-center pb-1">
                                <span class="font-serif italic text-lg sm:text-xl text-[#0d2a54] font-bold opacity-80">
                                    Dr. Hendra S., M.Ed.
                                </span>
                            </div>
                            <div class="border-t border-slate-400 pt-1">
                                <p class="text-[10px] sm:text-xs font-bold text-[#0d2a54] leading-tight">
                                    Dr. Hendra Setiawan
                                </p>
                                <p class="text-[8px] sm:text-[10px] text-slate-500">
                                    Direktur Eduja Learning Media
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Bar: Issue Date -->
                    <div class="relative z-10 pt-2 flex items-center justify-between text-[8px] sm:text-[10px] text-slate-400 font-mono">
                        <span>Tanggal Penerbitan: {issueDate}</span>
                        <span>Diverifikasi Secara Digital oleh Eduja Learning Media LMS</span>
                    </div>
                </div>
            </div>

            <!-- Footer Toolbar (Screen only) -->
            <div class="flex items-center justify-between px-5 py-3 bg-muted/30 border-t border-border/80 print:hidden text-xs text-muted-foreground shrink-0">
                <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-medium">
                    <CheckCircle2 class="size-3.5" />
                    Sertifikat resmi terverifikasi dan siap diunduh/dicetak format landscape A4.
                </span>
                <button
                    type="button"
                    onclick={onClose}
                    class="px-4 py-1.5 rounded-xl font-semibold bg-muted hover:bg-muted/80 text-foreground transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
{/if}

<style>
    @media print {
        :global(body) {
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        :global(body *) {
            visibility: hidden;
        }
        #certificate-print-area, #certificate-print-area * {
            visibility: visible;
        }
        #certificate-print-area {
            position: fixed !important;
            left: 50% !important;
            top: 50% !important;
            transform: translate(-50%, -50%) !important;
            width: 277mm !important;
            height: 190mm !important;
            max-width: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 12mm 16mm !important;
            page-break-after: always;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
