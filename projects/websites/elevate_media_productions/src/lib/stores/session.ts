import { writable } from 'svelte/store';

type SessionState = {
  user: Profile | null;
  loading: boolean;
};

function createSessionStore() {
  const { subscribe, set, update } = writable<SessionState>({
    user: null,
    loading: true
  });
  return {
    subscribe,
    set,
    update,
    reset: () => set({ user: null, loading: false })
  };
}

export const session = createSessionStore();