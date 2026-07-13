import { reactive, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

// ── Module-level state shared across all components ───────────────────────────
export const gateState = reactive({
    isOpen:        false,
    isSuccess:     false,
    isLoading:     false,
    method:        'password',
    credential:    '',
    errorMessage:  '',
    _resolve:      null,
})

export function cancelGate() {
    const resolve    = gateState._resolve
    gateState.isOpen = false
    resolve?.(false)
}

export async function submitGate() {
    if (gateState.isLoading) return
    gateState.isLoading    = true
    gateState.errorMessage = ''
    try {
        await axios.post(route('security.verify-gate'), {
            method:     gateState.method,
            credential: gateState.credential,
        })
        gateState.isLoading = false
        gateState.isSuccess = true
        setTimeout(() => {
            const resolve      = gateState._resolve
            gateState.isOpen   = false
            gateState.isSuccess = false
            gateState.credential = ''
            resolve?.(true)
        }, 1500)
    } catch (e) {
        gateState.isLoading    = false
        gateState.errorMessage = e.response?.data?.message || 'Verifikasi gagal.'
    }
}
// ─────────────────────────────────────────────────────────────────────────────

export function useSecurityGate() {
    const page = usePage()

    const bypassActive = computed(() => {
        const exp = page.props.bypassExpiresAt
        return !!exp && exp > Math.floor(Date.now() / 1000)
    })

    const has2fa = computed(() => !!page.props.auth?.user?.has_2fa)

    function requireGate() {
        if (bypassActive.value) return Promise.resolve(true)

        return new Promise(resolve => {
            gateState._resolve     = resolve
            gateState.isOpen       = true
            gateState.isSuccess    = false
            gateState.isLoading    = false
            gateState.credential   = ''
            gateState.errorMessage = ''
            gateState.method       = has2fa.value ? '2fa' : 'password'
        })
    }

    return { bypassActive, requireGate, has2fa }
}
