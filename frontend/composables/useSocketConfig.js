export function useSocketConfig() {
  var config = useRuntimeConfig();
  var socketUrl = config.public.socketUrl;

  return {
    socketUrl: socketUrl,
  };
}
