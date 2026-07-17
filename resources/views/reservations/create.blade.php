<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-sky-700">Reservas</p>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Nova reserva</h2>
            </div>
        </div>
    </x-slot>

    <div x-data="reservationWizard()" class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Progress Bar / Wizard Indicators -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <nav class="relative flex flex-col md:flex-row justify-between items-center gap-4">
                    <!-- Step 1 -->
                    <div class="flex items-center gap-3 cursor-pointer" @click="if(step > 1) step = 1">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition"
                              :class="step === 1 ? 'bg-sky-600 text-white ring-4 ring-sky-100' : (step > 1 ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500')">
                            <span x-show="step <= 1">1</span>
                            <span x-show="step > 1">✓</span>
                        </span>
                        <div class="text-left">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Passo 1</p>
                            <p class="text-sm font-bold text-slate-800">Datas</p>
                        </div>
                    </div>

                    <div class="hidden md:block h-px flex-1 bg-slate-200 max-w-[40px]"></div>

                    <!-- Step 2 -->
                    <div class="flex items-center gap-3 cursor-pointer" @click="if(step > 2 && selectedRoom) step = 2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition"
                              :class="step === 2 ? 'bg-sky-600 text-white ring-4 ring-sky-100' : (step > 2 ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500')">
                            <span x-show="step <= 2">2</span>
                            <span x-show="step > 2">✓</span>
                        </span>
                        <div class="text-left">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Passo 2</p>
                            <p class="text-sm font-bold text-slate-800">Quarto</p>
                        </div>
                    </div>

                    <div class="hidden md:block h-px flex-1 bg-slate-200 max-w-[40px]"></div>

                    <!-- Step 3 -->
                    <div class="flex items-center gap-3 cursor-pointer" @click="if(step > 3 && selectedGuest) step = 3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition"
                              :class="step === 3 ? 'bg-sky-600 text-white ring-4 ring-sky-100' : (step > 3 ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500')">
                            <span x-show="step <= 3">3</span>
                            <span x-show="step > 3">✓</span>
                        </span>
                        <div class="text-left">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Passo 3</p>
                            <p class="text-sm font-bold text-slate-800">Hóspede</p>
                        </div>
                    </div>

                    <div class="hidden md:block h-px flex-1 bg-slate-200 max-w-[40px]"></div>

                    <!-- Step 4 -->
                    <div class="flex items-center gap-3 cursor-pointer" @click="if(step > 4) step = 4">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition"
                              :class="step === 4 ? 'bg-sky-600 text-white ring-4 ring-sky-100' : (step > 4 ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500')">
                            <span x-show="step <= 4">4</span>
                            <span x-show="step > 4">✓</span>
                        </span>
                        <div class="text-left">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Passo 4</p>
                            <p class="text-sm font-bold text-slate-800">Extras</p>
                        </div>
                    </div>

                    <div class="hidden md:block h-px flex-1 bg-slate-200 max-w-[40px]"></div>

                    <!-- Step 5 -->
                    <div class="flex items-center gap-3 cursor-pointer" @click="if(step > 5) step = 5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition"
                              :class="step === 5 ? 'bg-sky-600 text-white ring-4 ring-sky-100' : (step > 5 ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500')">
                            <span x-show="step <= 5">5</span>
                            <span x-show="step > 5">✓</span>
                        </span>
                        <div class="text-left">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Passo 5</p>
                            <p class="text-sm font-bold text-slate-800">Pagamento</p>
                        </div>
                    </div>

                    <div class="hidden md:block h-px flex-1 bg-slate-200 max-w-[40px]"></div>

                    <!-- Step 6 -->
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition"
                              :class="step === 6 ? 'bg-sky-600 text-white ring-4 ring-sky-100' : 'bg-slate-100 text-slate-500'">
                            <span>6</span>
                        </span>
                        <div class="text-left">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Passo 6</p>
                            <p class="text-sm font-bold text-slate-800">Resumo</p>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Steps Container -->
            <div class="relative">
                @include('reservations.steps.step1')
                @include('reservations.steps.step2')
                @include('reservations.steps.step3')
                @include('reservations.steps.step4')
                @include('reservations.steps.step5')
                @include('reservations.steps.step6')
            </div>

        </div>
    </div>

    <!-- Alpine.js Component Script -->
    <script>
        function reservationWizard() {
            return {
                step: 1,
                // Step 1: Dates & Guests
                check_in: '',
                check_out: '',
                adults: 1,
                children: 0,
                
                // Step 2: Quarto
                rooms: [],
                selectedRoom: null,
                loadingRooms: false,
                
                // Step 3: Hóspede
                searchGuestQuery: '',
                guests: [],
                selectedGuest: null,
                loadingGuests: false,
                
                // Modal de Novo Hóspede
                showNewGuestModal: false,
                newGuest: {
                    full_name: '',
                    email: '',
                    phone: '',
                    document_type: 'CPF',
                    document_number: ''
                },
                savingGuest: false,
                guestErrors: {},

                // Step 4: Extras
                extras: [],

                // Step 5: Pagamento
                paymentMethod: 'pending',

                // Step 6: Resumo & Salvar
                notes: '',
                savingReservation: false,

                // Getter helpers
                get nights() {
                    if (!this.check_in || !this.check_out) return 0;
                    const start = new Date(this.check_in + 'T12:00:00');
                    const end = new Date(this.check_out + 'T12:00:00');
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    return isNaN(diffDays) ? 0 : diffDays;
                },

                get subtotal() {
                    if (!this.selectedRoom) return 0;
                    return this.nights * this.selectedRoom.base_price;
                },

                get extrasTotal() {
                    let total = 0;
                    if (this.extras.includes('cafe')) {
                        total += this.nights * this.adults * 40.00;
                    }
                    if (this.extras.includes('estacionamento')) {
                        total += this.nights * 30.00;
                    }
                    if (this.extras.includes('berco')) {
                        total += 50.00;
                    }
                    if (this.extras.includes('pet')) {
                        total += 80.00;
                    }
                    if (this.extras.includes('cama_extra')) {
                        total += 120.00;
                    }
                    return total;
                },

                get grandTotal() {
                    return this.subtotal + this.extrasTotal;
                },

                // Actions
                searchRooms() {
                    if (!this.check_in || !this.check_out) return;
                    this.loadingRooms = true;
                    this.selectedRoom = null;
                    
                    const params = new URLSearchParams({
                        check_in: this.check_in,
                        check_out: this.check_out,
                        adults: this.adults,
                        children: this.children
                    });

                    fetch(`/reservations/api/availability?${params.toString()}`)
                        .then(res => res.json())
                        .then(data => {
                            this.rooms = data.rooms || [];
                            this.step = 2;
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Erro ao buscar disponibilidade.');
                        })
                        .finally(() => {
                            this.loadingRooms = false;
                        });
                },

                searchGuests() {
                    if (!this.searchGuestQuery) return;
                    this.loadingGuests = true;
                    
                    const params = new URLSearchParams({
                        search: this.searchGuestQuery
                    });

                    fetch(`/reservations/api/guests?${params.toString()}`)
                        .then(res => res.json())
                        .then(data => {
                            this.guests = data || [];
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Erro ao buscar hóspedes.');
                        })
                        .finally(() => {
                            this.loadingGuests = false;
                        });
                },

                saveGuest() {
                    this.savingGuest = true;
                    this.guestErrors = {};

                    fetch('/reservations/api/guests', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.newGuest)
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (res.status === 422) {
                            this.guestErrors = data.errors || {};
                        } else if (res.ok) {
                            this.selectedGuest = data;
                            this.showNewGuestModal = false;
                            this.newGuest = { full_name: '', email: '', phone: '', document_type: 'CPF', document_number: '' };
                        } else {
                            alert('Erro ao salvar hóspede.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Erro ao salvar hóspede.');
                    })
                    .finally(() => {
                        this.savingGuest = false;
                    });
                },

                submitReservation() {
                    this.savingReservation = true;
                    
                    const body = {
                        check_in: this.check_in,
                        check_out: this.check_out,
                        adults: this.adults,
                        children: this.children,
                        room_id: this.selectedRoom.id,
                        guest_id: this.selectedGuest.id,
                        extras: this.extras,
                        payment_method: this.paymentMethod,
                        notes: this.notes
                    };

                    fetch('/reservations', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(body)
                    })
                    .then(async res => {
                        if (res.redirected) {
                            window.location.href = res.url;
                        } else if (res.ok) {
                            const data = await res.json();
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            }
                        } else {
                            const data = await res.json();
                            alert(data.message || 'Erro ao salvar reserva.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Erro ao salvar reserva.');
                    })
                    .finally(() => {
                        this.savingReservation = false;
                    });
                }
            };
        }
    </script>
</x-app-layout>
