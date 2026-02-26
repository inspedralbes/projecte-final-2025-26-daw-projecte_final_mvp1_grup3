export function useSocketConfig() {
  // A. Recuperar configuració de runtime
  var config = useRuntimeConfig();
  var socketUrl = config.public.socketUrl;

  // B. Retornar objecte de configuració
  return {
    socketUrl: socketUrl
  };
}
