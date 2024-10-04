import "./bulma.css";
import { useContext } from "react";
import { Context } from "./context/context";

function App() {
  const {
    onsent,
    Input,
    setInput,
    Result,
    setResult,
    ShowResult,
    setShowResult,
  } = useContext(Context);

  const handleGenerate = async () => {
    setShowResult(false);
    const response = await onsent();
    const data = await response.text();
    setResult(data);
    setShowResult(true);
  };

  
  return (
    <div className="smartwrite">
      <div className="section my-6">
        <div className="card my-6 ">
          <div className="container">
            <div className="columns is-centered">
              <div className="column">
                <div className="column is-narrow">
                  <div className="column p-1">
                    <figure className="image is-128x128 container">
                    <img src="https://i.ibb.co/rZfM2cB/lg.png" className="is-rounded" alt="Logo" />
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
                  onChange={(e) => setInput(e.target.value)}
                />

                <button
                  className="button is-black p-3 m-3"
                  onClick={handleGenerate}
                >
                  Generate
                </button>
                <button className="button is-black p-3 m-3">Post</button>

                {ShowResult && (
                  <div className="notification is-primary my-3">
                    <h2 className="title is-4">AI Response</h2>
                    <div dangerouslySetInnerHTML={{ __html: Result }} />
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default App;
