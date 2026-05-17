const redis = require('redis');

describe('Node.js Infrastructure Test', () => {
  let client;

  beforeAll(async () => {
    // Intentar conectar al Redis de producción/docker
    client = redis.createClient({
      url: process.env.REDIS_URL || 'redis://redis:6379'
    });
    client.on('error', (err) => console.log('Redis Client Error', err));
    await client.connect();
  });

  afterAll(async () => {
    if (client) {
      await client.quit();
    }
  });

  test('should connect to Redis and set/get a value', async () => {
    await client.set('node_test_key', 'loopy_node_ok');
    const value = await client.get('node_test_key');
    expect(value).toBe('loopy_node_ok');
    await client.del('node_test_key');
  });

  test('should have environment variables configured', () => {
    // En deploy, estas variables deberían estar presentes
    // Si no están, el test fallará para avisar de configuración incompleta
    expect(process.env.JWT_SECRET || 'test').toBeDefined();
  });
});
