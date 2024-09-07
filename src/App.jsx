import "./bulma.css";
import logo from "./assets/lg.png";

function App() {
  return (
    <div className="section my-6">
      <div className="card my-6 ">
        <div className="container">
          <div className="columns is-centered">
            <div className="column">
              <div className="column is-narrow">
                <div className="column p-1">
                  <figure class="image is-128x128 container">
                    <img src={logo} className="is-rounded" />
                  </figure>
                  <div className="column">
                    <h1 className="title is-3 has-text-centered">
                      SmartWrite AI
                    </h1>
                  </div>
                </div>
              </div>
              <input
                className="input p-3 m-3"
                type="text"
                placeholder="Topic"
              />
              <textarea
                className="textarea is-large p-3 m-3"
                placeholder=""
              ></textarea>
              <button className="button is-black p-3 m-3">Generate</button>
              <button className="button is-black p-3 m-3">Post</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default App;
