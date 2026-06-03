@php
    $otherUser = $conversation->otherParticipant(auth()->user());
    $currentUser = auth()->user();
    $isEmployer = $currentUser->isEmployer();
    $sendRoute = $isEmployer ? route('employer.messages.send', $conversation) : route('messages.send', $conversation);
    $readRoute = $isEmployer ? route('employer.messages.read', $conversation) : '';
    $indexRoute = $isEmployer ? route('employer.messages') : route('messages.index');
@endphp

<div class="flex-1 flex flex-col h-full" x-data="{ showInterviewModal: false, showHireModal: false }">
    <div class="px-4 py-3 border-b border-slate-200 bg-white flex items-center justify-between">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ $indexRoute }}" class="md:hidden -ml-2 p-1.5 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="relative flex-shrink-0">
                <div class="w-10 h-10 {{ $otherUser->isEmployer() ? 'bg-[#052E5C]' : 'bg-[#EB5233]' }} rounded-full flex items-center justify-center text-white font-semibold text-sm">
                    {{ substr($otherUser->name, 0, 1) }}
                </div>
                @if($otherUser->isOnline())
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
                @endif
            </div>
            <div class="min-w-0">
                <h2 class="font-semibold text-slate-900 text-sm truncate">{{ $otherUser->name }}</h2>
                <div class="flex items-center gap-2">
                    @if($conversation->job)
                        <span class="text-xs text-[#052E5C] font-medium truncate">{{ $conversation->job->title }}</span>
                        <span class="text-slate-300">·</span>
                    @endif
                    <span class="text-xs {{ $otherUser->isOnline() ? 'text-green-600' : 'text-slate-400' }}">
                        {{ $otherUser->isOnline() ? 'Active now' : ($otherUser->lastSeen() ? 'Last seen ' . \Carbon\Carbon::parse($otherUser->lastSeen())->diffForHumans() : 'Offline') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1">
            @if($isEmployer)
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="p-2 hover:bg-slate-100 rounded-lg transition-colors" title="More actions">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" class="absolute right-0 top-full mt-1 w-64 bg-white rounded-xl shadow-lg border border-slate-200 py-2 z-50">
                    <button @click="open = false; showInterviewModal = true" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                        <svg class="w-4 h-4 text-[#052E5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Send interview invitation
                    </button>
                    <button @click="open = false; showHireModal = true" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Send hiring decision
                    </button>
                </div>
            </div>
            @endif
            <button onclick="document.getElementById('attachment-input').click()" class="p-2 hover:bg-slate-100 rounded-lg transition-colors" title="Attach file">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
            </button>
        </div>
    </div>

    @if($isEmployer)
    {{-- Interview Invitation Modal --}}
    <div x-data="{ show: false }" x-show="showInterviewModal || show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-init="$watch('showInterviewModal', v => show = v)">
        <div class="fixed inset-0 bg-black/40" @click="showInterviewModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Schedule Interview</h3>
                <button @click="showInterviewModal = false" class="p-1.5 hover:bg-slate-100 rounded-lg">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="interview-form" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Interview Type</label>
                    <select name="interview_type" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-[#EB5233] focus:border-[#EB5233]">
                        <option value="phone">Phone Call</option>
                        <option value="video">Video Call</option>
                        <option value="onsite">On-site</option>
                        <option value="technical">Technical</option>
                        <option value="behavioral">Behavioral</option>
                        <option value="panel">Panel</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-[#EB5233] focus:border-[#EB5233]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Meeting Link (optional)</label>
                    <input type="url" name="meeting_link" placeholder="https://meet.google.com/..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-[#EB5233] focus:border-[#EB5233]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Location (optional)</label>
                    <input type="text" name="location" placeholder="Office address..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-[#EB5233] focus:border-[#EB5233]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notes (optional)</label>
                    <textarea name="notes" rows="3" placeholder="Additional details..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-[#EB5233] focus:border-[#EB5233]"></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 bg-[#EB5233] text-white rounded-lg hover:bg-[#d94728] transition-colors text-sm font-medium">
                    Send Invitation
                </button>
            </form>
        </div>
    </div>

    {{-- Hiring Decision Modal --}}
    <div x-data="{ show: false }" x-show="showHireModal || show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-init="$watch('showHireModal', v => show = v)">
        <div class="fixed inset-0 bg-black/40" @click="showHireModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Hiring Decision</h3>
                <button @click="showHireModal = false" class="p-1.5 hover:bg-slate-100 rounded-lg">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="decision-form" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Decision</label>
                    <select name="decision" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-[#EB5233] focus:border-[#EB5233]">
                        <option value="hired">✅ Accepted - Hire</option>
                        <option value="rejected">❌ Not Selected</option>
                        <option value="on_hold">⏳ On Hold</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Message (optional)</label>
                    <textarea name="notes" rows="4" placeholder="Add a personal message..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-[#EB5233] focus:border-[#EB5233]"></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 bg-[#EB5233] text-white rounded-lg hover:bg-[#d94728] transition-colors text-sm font-medium">
                    Send Decision
                </button>
            </form>
        </div>
    </div>
    @endif

    <div id="chat-messages" class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-slate-50/50 scroll-smooth">
        @forelse($conversation->messages()->with(['sender', 'reads'])->orderBy('created_at', 'desc')->paginate(50)->reverse() as $message)
            <div class="flex {{ $message->sender_id === $currentUser->id ? 'justify-end' : 'justify-start' }} animate-fade-in">
                <div class="max-w-[75%] sm:max-w-[70%] {{ $message->sender_id === $currentUser->id ? 'bg-[#EB5233] text-white' : 'bg-white text-slate-900 border border-slate-200' }} rounded-2xl px-4 py-2.5 shadow-sm">
                    @if($message->attachment_path && $message->isAttachmentImage())
                        <div class="mb-2 -mx-4 -mt-2.5 rounded-t-2xl overflow-hidden">
                            <img src="{{ $message->attachment_url }}" alt="{{ $message->attachment_name }}" class="w-full h-auto max-h-64 object-cover cursor-pointer" onclick="window.open(this.src)">
                        </div>
                    @endif

                    @if($message->message)
                        <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">{{ $message->message }}</p>
                    @endif

                    @if($message->attachment_path && !$message->isAttachmentImage())
                        <div class="mt-2 flex items-center gap-2 {{ $message->sender_id === $currentUser->id ? 'bg-white/15' : 'bg-slate-50' }} rounded-lg px-3 py-2">
                            <svg class="w-5 h-5 flex-shrink-0 {{ $message->sender_id === $currentUser->id ? 'text-white/80' : 'text-[#EB5233]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-medium truncate {{ $message->sender_id === $currentUser->id ? 'text-white' : 'text-slate-700' }}">
                                    {{ $message->attachment_name }}
                                </p>
                                <p class="text-[10px] {{ $message->sender_id === $currentUser->id ? 'text-white/70' : 'text-slate-400' }}">
                                    {{ $message->attachment_size ? round($message->attachment_size / 1024, 1) . ' KB' : '' }}
                                </p>
                            </div>
                            <a href="{{ $message->attachment_url }}" target="_blank" class="p-1.5 hover:bg-black/5 rounded-lg transition-colors flex-shrink-0">
                                <svg class="w-4 h-4 {{ $message->sender_id === $currentUser->id ? 'text-white/80' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </a>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-1 mt-1" data-message-id="{{ $message->id }}">
                        <span class="text-[10px] {{ $message->sender_id === $currentUser->id ? 'text-white/70' : 'text-slate-400' }}">
                            {{ $message->created_at->format('g:i A') }}
                        </span>
                        @if($message->sender_id === $currentUser->id)
                            <span class="read-status text-[10px]">
                                @if($message->reads->isNotEmpty())
                                    <span class="text-[#052E5C]/60">Read</span>
                                @elseif($message->is_read)
                                    <span class="text-white/70">✓✓</span>
                                @else
                                    <span class="text-white/50">✓</span>
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm border border-slate-200">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 font-medium text-sm">Start the conversation</p>
                    <p class="text-slate-400 text-xs mt-0.5">Send a message to {{ $otherUser->name }}</p>
                </div>
            </div>
        @endforelse
    </div>

    <div id="typing-indicator" class="px-4 py-1 min-h-[24px]"></div>

    <div class="px-4 py-3 border-t border-slate-200 bg-white">
        <form id="message-form" class="flex items-end gap-2">
            @csrf
            <input type="file" id="attachment-input" accept=".pdf,.docx,.doc,.png,.jpg,.jpeg,.gif,.webp" class="hidden" @change="handleAttachment">
            <div class="flex-1 relative">
                <textarea
                    id="message-input"
                    rows="1"
                    placeholder="Type a message..."
                    class="w-full px-4 py-2.5 pr-12 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-[#EB5233] focus:border-[#EB5233] placeholder-slate-400 resize-none transition-all"
                    style="max-height: 120px;"
                    @input="autoResize(this); sendTyping()"
                    @keydown.enter.prevent="submitForm()"
                ></textarea>
                <div class="absolute right-2 bottom-2 flex items-center gap-1">
                    <button
                        type="button"
                        id="emoji-btn"
                        class="p-1.5 hover:bg-slate-200 rounded-lg transition-colors"
                        title="Quick replies"
                        onclick="toggleQuickReplies()"
                    >
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <button
                type="submit"
                id="send-btn"
                class="px-4 py-2.5 bg-[#EB5233] text-white rounded-xl hover:bg-[#d94728] transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-sm"
                disabled
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span class="hidden sm:inline text-sm font-medium">Send</span>
            </button>
        </form>

        <div id="attachment-preview" class="hidden mt-2 p-2 bg-slate-50 rounded-lg border border-slate-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    <span id="attachment-name" class="text-sm text-slate-700 truncate"></span>
                    <span id="attachment-size" class="text-xs text-slate-400"></span>
                </div>
                <button type="button" onclick="clearAttachment()" class="p-1 hover:bg-slate-200 rounded transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div id="quick-replies" class="hidden mt-2 bg-slate-50 rounded-xl border border-slate-200 p-2 max-h-40 overflow-y-auto">
            <div class="grid grid-cols-1 gap-1">
                @if($isEmployer)
                    <button type="button" class="quick-reply-btn text-left px-3 py-2 text-sm text-slate-700 hover:bg-white hover:shadow-sm rounded-lg transition-all" data-message="Thank you for your application. We are reviewing your profile and will get back to you soon.">
                        <span class="text-xs text-[#052E5C] font-medium">Application acknowledgment</span>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">Thank you for your application...</p>
                    </button>
                    <button type="button" class="quick-reply-btn text-left px-3 py-2 text-sm text-slate-700 hover:bg-white hover:shadow-sm rounded-lg transition-all" data-message="We would like to invite you for an interview. Please let us know your availability for next week.">
                        <span class="text-xs text-[#052E5C] font-medium">Interview invitation</span>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">We would like to invite you...</p>
                    </button>
                    <button type="button" class="quick-reply-btn text-left px-3 py-2 text-sm text-slate-700 hover:bg-white hover:shadow-sm rounded-lg transition-all" data-message="Your application is under review. We appreciate your patience and will update you on the status soon.">
                        <span class="text-xs text-[#052E5C] font-medium">Application under review</span>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">Your application is under review...</p>
                    </button>
                    <button type="button" class="quick-reply-btn text-left px-3 py-2 text-sm text-slate-700 hover:bg-white hover:shadow-sm rounded-lg transition-all" data-message="We have moved your application to the next stage. We will be in touch with further details.">
                        <span class="text-xs text-[#052E5C] font-medium">Application progressed</span>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">We have moved your application...</p>
                    </button>
                @else
                    <button type="button" class="quick-reply-btn text-left px-3 py-2 text-sm text-slate-700 hover:bg-white hover:shadow-sm rounded-lg transition-all" data-message="Thank you for the opportunity. I am very excited about this position and look forward to hearing from you.">
                        <span class="text-xs text-[#EB5233] font-medium">Thank you</span>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">Thank you for the opportunity...</p>
                    </button>
                    <button type="button" class="quick-reply-btn text-left px-3 py-2 text-sm text-slate-700 hover:bg-white hover:shadow-sm rounded-lg transition-all" data-message="I confirm my attendance for the interview. Thank you for the invitation.">
                        <span class="text-xs text-[#EB5233] font-medium">Confirm attendance</span>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">I confirm my attendance...</p>
                    </button>
                    <button type="button" class="quick-reply-btn text-left px-3 py-2 text-sm text-slate-700 hover:bg-white hover:shadow-sm rounded-lg transition-all" data-message="I would like to reschedule the interview. Would it be possible to find an alternative time?">
                        <span class="text-xs text-[#EB5233] font-medium">Reschedule request</span>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">I would like to reschedule...</p>
                    </button>
                    <button type="button" class="quick-reply-btn text-left px-3 py-2 text-sm text-slate-700 hover:bg-white hover:shadow-sm rounded-lg transition-all" data-message="Thank you for your message. I will review the information and get back to you shortly.">
                        <span class="text-xs text-[#EB5233] font-medium">Acknowledgment</span>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">Thank you for your message...</p>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const conversationId = {{ $conversation->id }};
    const messagesContainer = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const messageForm = document.getElementById('message-form');
    const sendBtn = document.getElementById('send-btn');
    let typingTimer = null;
    let isTyping = false;

    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    messageInput.addEventListener('input', () => {
        sendBtn.disabled = !messageInput.value.trim() && !window.selectedAttachment;
        autoResize(messageInput);
    });

    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    }

    function sendTyping() {
        if (!typingTimer) {
            isTyping = true;
            fetch('{{ $isEmployer ? route("employer.messages", $conversation) : "/messages" }}/' + conversationId + '/typing', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({typing: true})
            });
        }
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            isTyping = false;
            fetch('{{ $isEmployer ? route("employer.messages", $conversation) : "/messages" }}/' + conversationId + '/typing', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({typing: false})
            });
            typingTimer = null;
        }, 2000);
    }

    function toggleQuickReplies() {
        const el = document.getElementById('quick-replies');
        el.classList.toggle('hidden');
    }

    document.querySelectorAll('.quick-reply-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            messageInput.value = this.dataset.message;
            sendBtn.disabled = false;
            document.getElementById('quick-replies').classList.add('hidden');
            autoResize(messageInput);
            messageInput.focus();
        });
    });

    window.selectedAttachment = null;

    document.getElementById('attachment-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        window.selectedAttachment = file;
        document.getElementById('attachment-name').textContent = file.name;
        document.getElementById('attachment-size').textContent = (file.size / 1024).toFixed(1) + ' KB';
        document.getElementById('attachment-preview').classList.remove('hidden');
        sendBtn.disabled = !messageInput.value.trim() && !file;
    });

    function clearAttachment() {
        window.selectedAttachment = null;
        document.getElementById('attachment-input').value = '';
        document.getElementById('attachment-preview').classList.add('hidden');
        sendBtn.disabled = !messageInput.value.trim();
    }

    messageForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = messageInput.value.trim();
        const attachment = window.selectedAttachment;
        if (!message && !attachment) return;

        sendBtn.disabled = true;
        const formData = new FormData();
        if (message) formData.append('message', message);
        if (attachment) formData.append('attachment', attachment);

        try {
            const response = await fetch('{{ $sendRoute }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: formData,
            });

            if (response.ok) {
                const data = await response.json();
                const msg = data.message;
                const isEmpty = messagesContainer.querySelector('.text-center.py-8') || messagesContainer.querySelector('.text-center');
                if (isEmpty) isEmpty.remove();

                const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                const html = `
                    <div class="flex justify-end animate-fade-in">
                        <div class="max-w-[75%] sm:max-w-[70%] bg-[#EB5233] text-white rounded-2xl px-4 py-2.5 shadow-sm">
                            ${msg.message ? `<p class="text-sm leading-relaxed whitespace-pre-wrap">${escHtml(msg.message)}</p>` : ''}
                            <div class="flex items-center justify-end gap-1 mt-1">
                                <span class="text-[10px] text-white/70">${time}</span>
                                <span class="text-[10px] text-white/50">✓</span>
                            </div>
                        </div>
                    </div>
                `;
                messagesContainer.insertAdjacentHTML('beforeend', html);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                messageInput.value = '';
                clearAttachment();
                autoResize(messageInput);
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
        sendBtn.disabled = false;
    });

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    if (window.Echo) {
        window.Echo.private('conversation.' + conversationId)
            .listen('MessageSent', (e) => {
                const isMine = e.sender_id === {{ $currentUser->id }};
                if (isMine) return;
                const container = document.getElementById('chat-messages');
                const time = new Date(e.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                const html = `
                    <div class="flex justify-start animate-fade-in">
                        <div class="max-w-[75%] sm:max-w-[70%] bg-white text-slate-900 border border-slate-200 rounded-2xl px-4 py-2.5 shadow-sm">
                            <p class="text-sm leading-relaxed">${escHtml(e.message)}</p>
                            <div class="flex items-center justify-end gap-1 mt-1">
                                <span class="text-[10px] text-slate-400">${time}</span>
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
                container.scrollTop = container.scrollHeight;
                const readUrl = '{{ $readRoute }}';
                if (readUrl) {
                    fetch(readUrl, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                    });
                }
            })
            .listen('MessageRead', (e) => {
                const readIndicator = document.querySelector(`[data-message-id="${e.message_id}"] .read-status`);
                if (readIndicator) {
                    readIndicator.innerHTML = '<span class="text-[10px] text-[#052E5C]/60">Read</span>';
                }
            })
            .listen('TypingIndicator', (e) => {
                const typingEl = document.getElementById('typing-indicator');
                if (typingEl) {
                    if (e.typing && e.user_id !== {{ $currentUser->id }}) {
                        typingEl.innerHTML = `<div class="flex items-center gap-2 text-xs text-slate-400 italic"><span class="flex gap-0.5"><span class="w-1 h-1 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0s"></span><span class="w-1 h-1 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></span><span class="w-1 h-1 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span></span> ${e.user_name} is typing...</div>`;
                    } else {
                        typingEl.innerHTML = '';
                    }
                }
            });
    }

    document.addEventListener('click', function(e) {
        const quickReplies = document.getElementById('quick-replies');
        const emojiBtn = document.getElementById('emoji-btn');
        if (quickReplies && !quickReplies.contains(e.target) && !emojiBtn.contains(e.target)) {
            quickReplies.classList.add('hidden');
        }
    });
</script>
@endpush

<style>
    .animate-fade-in {
        animation: fadeIn 0.2s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    #chat-messages {
        scrollbar-width: thin;
        scrollbar-color: #e2e8f0 transparent;
    }
    #chat-messages::-webkit-scrollbar {
        width: 4px;
    }
    #chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }
    #chat-messages::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 4px;
    }
    [x-cloak] { display: none !important; }
</style>

@if($isEmployer)
<script>
    document.getElementById('interview-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const res = await fetch('{{ route("employer.messages.interview.send", $conversation) }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: formData,
            });
            if (res.ok) {
                this.reset();
                window.showInterviewModal = false;
                location.reload();
            } else {
                const data = await res.json();
                alert(data.error || 'Failed to send invitation');
            }
        } catch (e) {
            alert('Failed to send interview invitation');
        }
    });

    document.getElementById('decision-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const res = await fetch('{{ route("employer.messages.decision.send", $conversation) }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: formData,
            });
            if (res.ok) {
                this.reset();
                window.showHireModal = false;
                location.reload();
            } else {
                const data = await res.json();
                alert(data.error || 'Failed to send decision');
            }
        } catch (e) {
            alert('Failed to send decision');
        }
    });
</script>
@endif
