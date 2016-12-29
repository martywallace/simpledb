#simpledb.

Execute common MySQL operations easily with this PDO wrapper.

## API.

<table>
    <tbody>
        <tr>
            <td><code>one(query: string, params?: array, class?: string)</code></td>
            <td>Return the first row from a query.</td>
        </tr>
        <tr>
            <td><code>all(query: string, params?: array, class?: string)</code></td>
            <td>Return all rows from a query.</td>
        </tr>
        <tr>
            <td><code>prop(query: string, params?: array, fallback?: any)</code></td>
            <td>Returns the value of the first column in the first record from a query. Useful for operations like <code>select value from config where key = 'token'</code>.</td>
        </tr>
    </tbody>
</table>